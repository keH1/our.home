<?php

namespace App\Enums;

enum NotificationType: string
{
    case NONE = 'none';
    case ADDRESS = 'address';
    case SYSTEM = 'system';
    case USER = 'user';
    case TESTIMONY_SUBMISSION = 'testimony_submission';
}
