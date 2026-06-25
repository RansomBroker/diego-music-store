<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard\Step;

class BranchForm
{
    /**
     * Get the wizard steps for Branch creation/edit.
     *
     * @return array<Step>
     */
    public static function getWizardSteps(): array
    {
        return [
            Step::make('General Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label('Branch Name'),

                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(50)
                        ->label('Phone Number'),
                ]),

            Step::make('Location & Status')
                ->schema([
                    Textarea::make('address')
                        ->rows(3)
                        ->maxLength(500)
                        ->label('Address'),

                    Toggle::make('is_active')
                        ->label('Is Active')
                        ->default(true),
                ]),
        ];
    }
}
