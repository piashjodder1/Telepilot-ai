<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CronStatus extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected string $view = 'filament.pages.cron-status';
}
