<?php

namespace App\Filament\Resources\PricingTiers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PricingTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Pricing Tier Name')
                    ->placeholder('e.g., Emas, Perak, Grosir'),

                Textarea::make('description')
                    ->maxLength(500)
                    ->rows(3)
                    ->label('Description')
                    ->placeholder('Enter pricing tier description...'),
            ]);
    }
}
