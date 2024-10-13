<?php

namespace App\Enums;

enum RpcApiMapper: string
{
    case LOGIN = 'user_procedure@login';
    case GET_USER_DATA = 'user_procedure@getUserData';
    case REGISTER = 'user_procedure@register';
    case PING = 'user_procedure@ping';
    case CHANGE_PASSWORD = 'user_procedure@changePassword';

    case GET_ALL_STREETS = 'house_procedure@getAllStreets';
    case GET_HOUSE_BY_STREET = 'house_procedure@getHousesByStreet';
    case GET_APARTMENTS_WITH_COUNTERS = 'house_procedure@getApartmentsWithCounters';
    case SEND_COUNTERS_DATA = 'counter_procedure@acceptHouseCounters';
    case CREATE_PAID_SERVICE_CATEGORY = 'paid_service_category@createPaidServiceCategory';
    case CREATE_PAID_SERVICE = 'paid_service@createPaidService';
    case UPDATE_PAID_SERVICE = 'paid_service@updatePaidService';
    case GET_PAID_SERVICES = 'paid_service@getPaidServices';
    case CREATE_PAID_CLAIM = 'claim@createPaidClaim';
}
