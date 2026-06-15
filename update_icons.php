<?php

$resources = [
    'AiModels\AiModelResource.php' => ['heroicon-o-cpu-chip', 'Autopilot'],
    'Channels\ChannelResource.php' => ['heroicon-o-chat-bubble-left-right', 'Telegram'],
    'Drafts\DraftResource.php' => ['heroicon-o-document-text', 'Content'],
    'ScheduledPosts\ScheduledPostResource.php' => ['heroicon-o-calendar', 'Content'],
    'PublishedPosts\PublishedPostResource.php' => ['heroicon-o-check-circle', 'Content'],
    'TelegramAccounts\TelegramAccountResource.php' => ['heroicon-o-envelope', 'Telegram'],
    'HeartbeatLogs\HeartbeatLogResource.php' => ['heroicon-o-clock', 'Settings'],
    'SystemLogs\SystemLogResource.php' => ['heroicon-o-queue-list', 'Analytics'],
    'Topics\TopicResource.php' => ['heroicon-o-hashtag', null, true],
];

foreach ($resources as $file => $config) {
    $path = "app/Filament/Resources/" . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        $icon = $config[0];
        $group = $config[1] ?? '';
        $hide = $config[2] ?? false;

        $replace = "protected static ?string \$navigationIcon = '$icon';";
        if ($group) {
            $replace .= "\n    protected static ?string \$navigationGroup = '$group';";
        }
        if ($hide) {
            $replace .= "\n    protected static bool \$shouldRegisterNavigation = false;";
        }
        
        // Remove existing navigationGroup if it exists
        $content = preg_replace('/protected static \?string \$navigationGroup = \'.*?\';/s', '', $content);

        // Replace navigationIcon with the new icon and group
        $content = preg_replace('/protected static string\|BackedEnum\|null \$navigationIcon = .*?;/s', $replace, $content);
        $content = preg_replace('/protected static \?string \$navigationIcon = \'.*?\';/s', $replace, $content);

        file_put_contents($path, $content);
    }
}
