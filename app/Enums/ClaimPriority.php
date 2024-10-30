<?php

namespace App\Enums;

enum ClaimPriority: string
{
    case STANDARD = 'standard';
    case HIGH = 'high';
    case URGENT = 'urgent';
}
