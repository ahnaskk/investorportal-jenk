<?php

return [
    'pusher_app_key' => env('PUSHER_APP_KEY'),
    'pusher_cluster' => env('PUSHER_CLUSTER'),
    'pusher_app_secret' => env('PUSHER_APP_SECRET'),
    'pusher_app_id' => env('PUSHER_APP_ID'),
    'pusher_app_cluster' => env('PUSHER_APP_CLUSTER'),
    'app_env' => env('APP_ENV'),

    'api_datalogger' => env('API_DATALOGGER', true),

    'actum_parent_id_merchant' => env('ACTUM_PARENT_ID_MERCHANT'),
    'actum_sub_id_merchant' => env('ACTUM_SUB_ID_MERCHANT'),
    'actum_username_merchant' => env('ACTUM_USERNAME_MERCHANT'),
    'actum_password_merchant' => env('ACTUM_PASSWORD_MERCHANT'),

    'actum_parent_id_investor_to_velocity' => env('ACTUM_PARENT_ID_INVESTOR_TO_VELOCITY'),
    'actum_sub_id_investor_to_velocity' => env('ACTUM_SUB_ID_INVESTOR_TO_VELOCITY'),
    'actum_username_investor_to_velocity' => env('ACTUM_USERNAME_INVESTOR_TO_VELOCITY'),
    'actum_password_investor_to_velocity' => env('ACTUM_PASSWORD_INVESTOR_TO_VELOCITY'),

    'actum_parent_id_velocity_to_investor' => env('ACTUM_PARENT_ID_VELOCITY_TO_INVESTOR'),
    'actum_sub_id_velocity_to_investor' => env('ACTUM_SUB_ID_VELOCITY_TO_INVESTOR'),
    'actum_username_velocity_to_investor' => env('ACTUM_USERNAME_VELOCITY_TO_INVESTOR'),
    'actum_password_velocity_to_investor' => env('ACTUM_PASSWORD_VELOCITY_TO_INVESTOR'),

    'ach_user_id' => env('ACH_USER_ID'),

    'communication_portal_sendor_id' => env('COMMUNICATION_PORTAL_SENDOR_ID', ''),
    'communication_portal_website' => env('COMMUNICATION_PORTAL_WEBSITE', 'https:://'),
    'communication_portal_api_key' => env('COMMUNICATION_PORTAL_API_KEY', ''),
];
