<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Wizard\Step;

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
                        ->label('Branch Name')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if (empty($get('store_name')) || $get('store_name') === $state) {
                                $set('store_name', $state);
                            }
                        }),

                    TextInput::make('store_name')
                        ->maxLength(255)
                        ->label('Store / Shop Name')
                        ->helperText('Defaults to Branch Name if left empty'),

                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(50)
                        ->label('Phone Number'),

                    FileUpload::make('logo_path')
                        ->image()
                        ->directory('branch-logos')
                        ->label('Store Logo')
                        ->columnSpanFull(),
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
