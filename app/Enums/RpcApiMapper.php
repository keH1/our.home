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
    case GET_APARTMENT_DATA_BY_ID = 'house_procedure@getApartmentDataById';
    case SEND_COUNTERS_DATA = 'counter_procedure@acceptHouseCounters';
    case CREATE_PAID_SERVICE_CATEGORY = 'paid_service_category@createPaidServiceCategory';
    case GET_PAID_SERVICE_CATEGORIES = 'paid_service_category@getPaidServiceCategories';
    case CREATE_PAID_SERVICE = 'paid_service@createPaidService';
    case UPDATE_PAID_SERVICE = 'paid_service@updatePaidService';
    case DELETE_PAID_SERVICE = 'paid_service@deletePaidService';
    case GET_PAID_SERVICES = 'paid_service@getPaidServices';
    case CREATE_CLAIM = 'claim@createClaim';
    case UPDATE_CLAIM = 'claim@updateClaim';
    case GET_CLAIMS = 'claim@getClaims';
    case CREATE_WORKER = 'worker@createWorker';
    case EDIT_WORKER = 'worker@editWorker';
    case GET_WORKERS = 'worker@getWorkers';
    case CREATE_WORKER_CATEGORY = 'worker_category@createWorkerCategory';
    case GET_WORKER_CATEGORIES = 'worker_category@getWorkerCategories';
    case SEND_CLAIM_CHAT_MESSAGE = 'claim_message@createMessage';
    case GET_CLAIM_CHAT_MESSAGES = 'claim_message@getClaimChat';
    case GET_CLAIM_CATEGORIES = 'claim_category@getCategories';
    case SEND_CLAIM_FEEDBACK = 'claim_review@createReview';
}
