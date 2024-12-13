<?php

namespace App\Enums;

enum Permissions: string
{
    case FULL = 'полный доступ';
    case NORMAL = 'обычный доступ';
}
