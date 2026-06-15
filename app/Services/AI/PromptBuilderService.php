<?php

namespace App\Services\AI;

use App\Models\Rule;
use App\Models\Topic;

class PromptBuilderService
{
    public function build(Rule $rule, ?Topic $topic = null): string
    {
        $prompt = "You are an expert social media content creator for Telegram.\n";
        
        $currentTopic = $topic ? $topic->topic : $rule->topic;
        $prompt .= "Topic: {$currentTopic}\n";
        
        $prompt .= "Tone: {$rule->tone}\n";
        $prompt .= "Language: {$rule->language}\n";
        
        if ($rule->format === 'poll') {
            $prompt .= "Format: Create a Telegram poll. Provide the question and 3-4 options.\n";
            $prompt .= "You MUST output ONLY a valid JSON object in this exact format: {\"question\": \"The poll question\", \"options\": [\"Option 1\", \"Option 2\", \"Option 3\"]}\n";
        } elseif ($rule->format === 'photo_caption') {
            $prompt .= "Format: Create a photo caption with relevant emojis and hashtags.\n";
            $prompt .= "You MUST output ONLY a valid JSON object in this exact format: {\"content\": \"The caption text here\"}\n";
        } else {
            $prompt .= "Format: Write a concise, engaging text post suitable for a Telegram channel.\n";
            $prompt .= "You MUST output ONLY a valid JSON object in this exact format: {\"content\": \"The post text here\"}\n";
        }

        // Add AI Memory (prevent repetition)
        $recentDrafts = \App\Models\Draft::where('channel_id', $rule->channel_id)
            ->where('status', 'published')
            ->latest()
            ->take(3)
            ->get();

        if ($recentDrafts->isNotEmpty()) {
            $prompt .= "\nTo ensure variety, here are the last few posts that were already published. DO NOT write about the exact same things, but keep the overall theme consistent:\n";
            foreach ($recentDrafts as $idx => $draft) {
                $num = $idx + 1;
                $prompt .= "Previous Post #{$num}:\n\"{$draft->content}\"\n\n";
            }
        }

        $prompt .= "Constraint: Do not include any introductory or concluding remarks, only output the requested content directly.";

        return $prompt;
    }
}
