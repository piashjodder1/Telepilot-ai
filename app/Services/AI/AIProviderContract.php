<?php

namespace App\Services\AI;

interface AIProviderContract
{
    public function generate(string $prompt): ?string;
}
