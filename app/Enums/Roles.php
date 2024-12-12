<?php

namespace App\Enums;

enum Roles: string
{
    case ADMIN = 'Администратор';
    case USER = 'Пользователь';
    case GUEST = 'Гость';

    public function permissions(): array
    {
        return match($this)
        {
            self::ADMIN => [
                Permissions::FULL,
                Permissions::NORMAL,
            ],
            self::USER => [
                Permissions::NORMAL
            ],
            self::GUEST => [],
        };
    }
}
