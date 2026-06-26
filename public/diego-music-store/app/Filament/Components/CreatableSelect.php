<?php

namespace App\Filament\Components;

use Closure;
use Filament\Forms\Components\Field;

class CreatableSelect extends Field
{
    protected string $view = 'filament.components.creatable-select';

    protected array|Closure $options = [];

    public function options(array|Closure $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->evaluate($this->options);
    }
}
