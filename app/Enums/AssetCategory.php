<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum AssetCategory: string
{
    use HasTranslatableLabel;

    case Building = 'building';
    case Vehicle = 'vehicle';
    case Furniture = 'furniture';
    case Electronics = 'electronics';
    case Musical = 'musical';
    case Equipment = 'equipment';
    case Land = 'land';
    case Other = 'other';

}
