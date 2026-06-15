<?php

namespace App\Services\AI\Providers;

use App\Models\AiModel;
use App\Services\AI\AIProviderContract;
use Illuminate\Support\Facades\Http;

class GeminiProvider implements AIProviderContract
{
    public function __construct(protected AiModel $model) {}

    public function generate(string $prompt): ?string
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model->model}:generateContent?key={$this->model->api_key}";

        $response = Http::timeout($this->model->timeout_seconds)
            ->retry($this->model->retry_attempts, 1000)
            ->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => (float) $this->model->temperature,
                    'maxOutputTokens' => (int) $this->model->max_tokens,
                ]
            ]);

        if ($response->successful()) {
            return $response->json('candidates.0.content.parts.0.text');
        }

        throw new \Exception("Gemini API Error: " . $response->body());
    }
}
