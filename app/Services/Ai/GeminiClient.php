<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Gemini client (generateContent REST API, plain HTTP). Asks for a JSON answer that follows a schema.
 */
class GeminiClient implements LlmClient
{
    private const RETRYABLE = [429, 500, 502, 503, 504];
    private const BASE = 'https://generativelanguage.googleapis.com/v1beta/models/';

    private float $deadline = 0;

    private function secondsLeft(): int
    {
        return (int) floor($this->deadline - microtime(true));
    }

    public function structured(string $system, string $user, array $schema, ?int $maxTokens = null, ?array $image = null): array
    {
        $key = config('ai.api_key');
        if (blank($key)) {
            throw new AiException('The AI is not configured yet: set GEMINI_API_KEY in the .env file.');
        }

        // One time budget for the whole request, retries included, kept below PHP's own limit
        @set_time_limit(config('ai.timeout') + 30);
        $this->deadline = microtime(true) + config('ai.timeout');

        // Gemini has accepted the schema in more than one shape over time. Try the full JSON Schema first,
        // then the older OpenAPI-style one, then plain JSON mode (the prompt already names the fields).
        $variants = [
            ['responseJsonSchema' => $schema],
            ['responseSchema' => $this->withoutUnsupported($schema)],
            [],
        ];

        // Less "thinking" makes a blog draft several times faster; it can be turned off with AI_GEMINI_THINKING=
        $thinking = config('ai.gemini_thinking');

        $build = function (array $schemaPart, bool $withThinking) use ($system, $user, $schema, $maxTokens, $thinking, $image) {
            $config = array_merge([
                'responseMimeType' => 'application/json',
                'maxOutputTokens' => $maxTokens ?? config('ai.max_tokens'),
            ], $schemaPart);
            if ($withThinking && filled($thinking)) {
                $config['thinkingConfig'] = ['thinkingLevel' => $thinking];
            }

            return [
                'systemInstruction' => ['parts' => [['text' => $system]]],
                'contents' => [['role' => 'user', 'parts' => array_merge(
                    $image ? [['inlineData' => ['mimeType' => $image['mime'], 'data' => $image['data']]]] : [],
                    [['text' => $user . ($schemaPart ? '' : $this->schemaHint($schema))]],
                )]],
                'generationConfig' => $config,
            ];
        };

        $response = null;
        foreach ($variants as $i => $schemaPart) {
            $last = $i === count($variants) - 1;

            $response = $this->send($key, $build($schemaPart, true), throwOn400: false);

            // The model may not accept the thinking setting: retry the same request without it
            if ($response->status() === 400 && filled($thinking) && stripos((string) $response->json('error.message'), 'thinking') !== false) {
                Log::info('Gemini rejected the thinking setting: ' . $response->json('error.message'));
                $response = $this->send($key, $build($schemaPart, false), throwOn400: false);
            }

            if ($response->status() !== 400) break;

            Log::info('Gemini rejected schema variant ' . $i . ': ' . $response->json('error.message'));
            if ($last) throw $this->errorFor($response);
        }

        $json = $response->json();
        if (!is_array($json)) {
            throw new AiException('The AI returned an unreadable answer. Please try again.');
        }

        if (!empty($json['promptFeedback']['blockReason'])) {
            throw new AiException('The AI declined to write this. Try rephrasing the topic.');
        }

        $candidate = $json['candidates'][0] ?? null;
        $finish = $candidate['finishReason'] ?? null;
        if (in_array($finish, ['SAFETY', 'PROHIBITED_CONTENT', 'BLOCKLIST', 'SPII', 'RECITATION'], true)) {
            throw new AiException('The AI declined to write this. Try rephrasing the topic.');
        }
        if ($finish === 'MAX_TOKENS') {
            throw new AiException('The answer was cut off because it was too long. Choose a shorter length and try again.');
        }

        $text = '';
        foreach ($candidate['content']['parts'] ?? [] as $part) {
            // "thought" parts are the model's reasoning summary, not the answer
            if (!empty($part['thought'])) continue;
            $text .= $part['text'] ?? '';
        }

        $data = $this->decode($text);
        if (!is_array($data)) {
            throw new AiException('The AI answer was not in the expected format. Please try again.');
        }

        $usage = $json['usageMetadata'] ?? [];

        return [
            'data' => $data,
            'input_tokens' => (int) ($usage['promptTokenCount'] ?? 0),
            'output_tokens' => (int) (($usage['candidatesTokenCount'] ?? 0) + ($usage['thoughtsTokenCount'] ?? 0)),
            'model' => (string) ($json['modelVersion'] ?? config('ai.model')),
        ];
    }

    private function send(string $key, array $body, bool $throwOn400): Response
    {
        $attempts = 3;
        $url = self::BASE . config('ai.model') . ':generateContent';

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            if ($this->secondsLeft() < 5) {
                throw new AiException('The AI took too long to answer. It may be busy: try again, or switch to a faster model with AI_GEMINI_MODEL.');
            }

            try {
                $response = Http::withHeaders(['x-goog-api-key' => $key])
                    ->acceptJson()
                    ->asJson()
                    ->timeout($this->secondsLeft())
                    ->post($url, $body);
            } catch (ConnectionException $e) {
                $timedOut = str_contains(strtolower($e->getMessage()), 'timed out');
                if ($attempt < $attempts && !$timedOut && $this->secondsLeft() > 10) {
                    sleep(2 * $attempt);
                    continue;
                }
                Log::warning('Gemini API connection failed: ' . $e->getMessage());
                throw new AiException($timedOut
                    ? 'The AI took too long to answer. It may be busy: try again, or switch to a faster model with AI_GEMINI_MODEL.'
                    : 'Could not reach the AI service. Check the server\'s internet access and try again.');
            }

            if ($response->successful()) {
                return $response;
            }

            if ($response->status() === 400 && !$throwOn400) {
                return $response; // caller tries the next schema variant
            }

            if ($attempt < $attempts && in_array($response->status(), self::RETRYABLE, true) && $this->secondsLeft() > 15) {
                sleep(min((int) $response->header('retry-after') ?: 2 * $attempt, 10));
                continue;
            }

            throw $this->errorFor($response);
        }

        throw new AiException('The AI request failed. Please try again.');
    }

    private function errorFor(Response $response): AiException
    {
        $status = $response->status();
        $detail = $response->json('error.message');
        Log::warning("Gemini API error {$status}: " . ($detail ?? $response->body()));

        $keyProblem = $status === 400 && is_string($detail) && stripos($detail, 'api key') !== false;

        return new AiException(match (true) {
            $status === 401, $status === 403, $keyProblem => 'The Gemini API key was rejected. Check GEMINI_API_KEY in the .env file.',
            $status === 404 => 'The configured AI model was not found. Check AI_GEMINI_MODEL in the .env file.',
            $status === 429 => 'Gemini\'s usage limit was reached (the free tier has per-minute and per-day limits). Wait a bit and try again.',
            $status >= 500 => 'Google\'s AI model is overloaded right now. Try again in a minute, or switch to a lighter model with AI_GEMINI_MODEL (for example gemini-3.5-flash-lite).',
            default => 'The AI request was rejected' . ($detail ? ': ' . $detail : '.'),
        });
    }

    /** JSON mode can still wrap the answer in a Markdown code fence. */
    private function decode(string $text): mixed
    {
        $text = trim($text);
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $text, $m)) {
            $text = $m[1];
        }

        return json_decode($text, true);
    }

    /** The older responseSchema format rejects some standard JSON Schema keywords. */
    private function withoutUnsupported(array $schema): array
    {
        unset($schema['additionalProperties']);
        foreach ($schema as $key => $value) {
            if (is_array($value)) {
                $schema[$key] = $this->withoutUnsupported($value);
            }
        }

        return $schema;
    }

    private function schemaHint(array $schema): string
    {
        return "\n\nReturn only a JSON object that follows this JSON Schema:\n" . json_encode($schema, JSON_UNESCAPED_SLASHES);
    }
}
