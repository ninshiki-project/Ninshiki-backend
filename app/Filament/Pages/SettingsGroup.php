<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SettingsGroup extends Page
{
    /**
     * This page will be used only to group other settings pages/resources
     */
    protected static ?string $title = 'Settings';

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static string $view = 'filament.pages.settings-group';

    protected static ?int $navigationSort = 5;
}
