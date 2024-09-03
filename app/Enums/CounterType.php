<?php

namespace App\Enums;

enum CounterType: string
{
    case ELECTRICITY = 'electricity';
    case WATER = 'water';
    case GAS = 'gas';
}
