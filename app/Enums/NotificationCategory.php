<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case GENERAL = 'general';
    case IMPORTANT = 'important';
    case CRITICAL = 'critical';
}
