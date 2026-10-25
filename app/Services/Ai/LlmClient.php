<?php

namespace App\Services\Ai;

/**
 * Anything that can answer a prompt with JSON matching a schema (Claude or Gemini).
 */
interface LlmClient
{
    /**
     * @param array $schema JSON Schema the answer must follow
     * @return array{data: array, input_tokens: int, output_tokens: int, model: string}
     * @throws AiException with a message that is safe to show in the admin
     */
    public function structured(string $system, string $user, array $schema, ?int $maxTokens = null): array;
}
