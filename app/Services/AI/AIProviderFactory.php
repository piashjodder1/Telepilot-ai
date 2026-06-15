<?php

namespace App\Services\AI;

use App\Models\AiModel;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\OpenCodeProvider;
use App\Services\AI\Providers\OpenRouterProvider;
use App\Services\AI\Providers\CustomProvider;

class AIProviderFactory
{
    public static function make(AiModel $model): AIProviderContract
    {
        return match($model->provider) {
            'openai'     => new OpenAIProvider($model),
            'gemini'     => new GeminiProvider($model),
            'opencode'   => new OpenCodeProvider($model),
            'openrouter' => new OpenRouterProvider($model),
            'custom'     => new CustomProvider($model),
            default      => throw new \Exception("Unsupported provider: {$model->provider}"),
        };
    }
}
