<?php

namespace App\Enums;

enum CounterType: string
{
    case ELECTRICITY = 'electricity';
    case COLD_WATER = 'cold_water';
    case WARM_WATER = 'warm_water';
    case GAS = 'gas';
}
