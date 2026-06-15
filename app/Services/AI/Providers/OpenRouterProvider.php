<?php

namespace App\Services\AI\Providers;

use App\Models\AiModel;
use App\Services\AI\AIProviderContract;
use Illuminate\Support\Facades\Http;

class OpenRouterProvider implements AIProviderContract
{
    public function __construct(protected AiModel $model) {}

    public function generate(string $prompt): ?string
    {
        $url = 'https://openrouter.ai/api/v1/chat/completions';

        $response = Http::withToken($this->model->api_key)
            ->timeout($this->model->timeout_seconds)
            ->retry($this->model->retry_attempts, 1000)
            ->post($url, [
                'model' => $this->model->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => (float) $this->model->temperature,
                'max_tokens' => (int) $this->model->max_tokens,
                'http_referer' => env('APP_URL'),
                'x-title' => env('APP_NAME'),
            ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        throw new \Exception("OpenRouter API Error: " . $response->body());
    }
}
