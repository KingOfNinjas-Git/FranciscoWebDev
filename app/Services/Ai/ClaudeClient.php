<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Minimal Claude Messages API client (plain HTTP, so no extra Composer package has to be deployed).
 * Always asks for a JSON answer that matches a schema, using structured outputs.
 */
class ClaudeClient implements LlmClient
{
    private const RETRYABLE = [429, 500, 502, 503, 504, 529];

    /**
     * @param array $schema JSON Schema the answer must follow (objects need additionalProperties: false)
     * @return array{data: array, input_tokens: int, output_tokens: int, model: string}
     */
    public function structured(string $system, string $user, array $schema, ?int $maxTokens = null): array
    {
        $key = config('ai.api_key');
        if (blank($key)) {
            throw new AiException('The AI is not configured yet: set ANTHROPIC_API_KEY (or GEMINI_API_KEY) in the .env file.');
        }

        // The model can think for a while before answering; give PHP time to wait for it
        @set_time_limit(config('ai.timeout') + 30);

        $body = [
            'model' => config('ai.model'),
            'max_tokens' => $maxTokens ?? config('ai.max_tokens'),
            'system' => $system,
            'messages' => [['role' => 'user', 'content' => $user]],
            'output_config' => [
                'effort' => config('ai.effort'),
                'format' => ['type' => 'json_schema', 'schema' => $schema],
            ],
        ];

        $response = $this->send($key, $body);

        $json = $response->json();
        if (!is_array($json)) {
            throw new AiException('The AI returned an unreadable answer. Please try again.');
        }

        $stop = $json['stop_reason'] ?? null;
        if ($stop === 'refusal') {
            throw new AiException('The AI declined to write this. Try rephrasing the topic.');
        }
        if ($stop === 'max_tokens') {
            throw new AiException('The answer was cut off because it was too long. Choose a shorter length and try again.');
        }

        $text = null;
        foreach ($json['content'] ?? [] as $block) {
            if (($block['type'] ?? null) === 'text') {
                $text = $block['text'] ?? '';
                break;
            }
        }

        $data = is_string($text) ? json_decode($text, true) : null;
        if (!is_array($data)) {
            throw new AiException('The AI answer was not in the expected format. Please try again.');
        }

        return [
            'data' => $data,
            'input_tokens' => (int) ($json['usage']['input_tokens'] ?? 0),
            'output_tokens' => (int) ($json['usage']['output_tokens'] ?? 0),
            'model' => (string) ($json['model'] ?? config('ai.model')),
        ];
    }

    private function send(string $key, array $body): Response
    {
        $attempts = 3;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $response = Http::withHeaders([
                    'x-api-key' => $key,
                    'anthropic-version' => config('ai.api_version'),
                ])
                    ->acceptJson()
                    ->asJson()
                    ->timeout(config('ai.timeout'))
                    ->post(config('ai.api_url'), $body);
            } catch (ConnectionException $e) {
                // A timeout would just time out again (and triple the wait): only retry real connection hiccups
                if ($attempt < $attempts && !str_contains(strtolower($e->getMessage()), 'timed out')) {
                    sleep(2 * $attempt);
                    continue;
                }
                Log::warning('Claude API connection failed: ' . $e->getMessage());
                throw new AiException(str_contains(strtolower($e->getMessage()), 'timed out')
                    ? 'The AI took too long to answer. Try a shorter length or a lower AI_EFFORT.'
                    : 'Could not reach the AI service. Check the server\'s internet access and try again.');
            }

            if ($response->successful()) {
                return $response;
            }

            if ($attempt < $attempts && in_array($response->status(), self::RETRYABLE, true)) {
                $wait = (int) $response->header('retry-after') ?: 2 * $attempt;
                sleep(min($wait, 10));
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
        Log::warning("Claude API error {$status}: " . ($detail ?? $response->body()) . ' (request-id ' . $response->header('request-id') . ')');

        return new AiException(match (true) {
            $status === 401 => 'The Anthropic API key was rejected. Check ANTHROPIC_API_KEY in the .env file.',
            $status === 402 => 'The Anthropic account has a billing problem. Check your plan and credits in the Anthropic console.',
            $status === 403 => 'The Anthropic API key is not allowed to use this model.',
            $status === 404 => 'The configured AI model was not found. Check AI_MODEL in the .env file.',
            $status === 429 => 'The AI service is rate limiting requests. Wait a minute and try again.',
            $status === 529, $status >= 500 => 'The AI service is busy right now. Try again in a few minutes.',
            default => 'The AI request was rejected' . ($detail ? ': ' . $detail : '.'),
        });
    }
}
