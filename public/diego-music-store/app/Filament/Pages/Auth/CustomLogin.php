<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

class CustomLogin extends BaseLogin
{
    /**
     * Get the credentials from the form data to authenticate with.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $loginType = filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $loginType => $data['email'],
            'password' => $data['password'],
        ];
    }

    /**
     * Overwrite email form component to accept both username and email.
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Username or Email')
            ->required()
            ->autocomplete()
            ->autofocus();
    }
}
