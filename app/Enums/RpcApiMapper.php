<?php

namespace App\Enums;

enum RpcApiMapper: string
{
    case LOGIN = 'user_procedure@login';
    case GET_USER_DATA = 'user_procedure@getUserData';
    case REGISTER = 'user_procedure@register';
    case PING = 'user_procedure@ping';
    case CHANGE_PASSWORD = 'user_procedure@changePassword';
}
