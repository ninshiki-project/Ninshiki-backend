<?php

namespace App\Filament\Resources\RedeemResource\Pages;

use App\Filament\Resources\RedeemResource;
use Filament\Resources\Pages\ManageRecords;

class ManageRedeems extends ManageRecords
{
    protected static string $resource = RedeemResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
