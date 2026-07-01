<?php

namespace App\Enums\Concerns;

use BackedEnum;
use Illuminate\Support\Str;

trait HasTranslatableLabel
{
    public function label(): string
    {
        if (! $this instanceof BackedEnum) {
            return Str::headline($this->name);
        }

        return __('enums.'.Str::snake(class_basename($this)).'.'.$this->value);
    }
}
