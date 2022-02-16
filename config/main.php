<?php

return [
    'api' => [
      'url' => env('API_URL', '')
    ],
    'jwt' => [
        'host_login' => env('JWT_HOST_LOGIN', ''),
        'login' => env('JWT_LOGIN', ''),
        'password' => env('JWT_PASSWORD', ''),

        'firstname' => env('JWT_FIRSTNAME', ''),
        'lastname' => env('JWT_LASTNAME', ''),
        'middleName' => env('JWT_MIDDLENAME', ''),

        'salt_jwt' => env('JWT_SALT_JWT', ''),
        'iss' => env('JWT_ISS', ''),
        'aud' => env('JWT_AUD', ''),
        'headEmail' => env('JWT_HEADEMAIL', ''),
        'account_id' => env('JWT_ACCOUNT_ID', ''),
        'lk_id' => env('JWT_LK_ID', ''),
        'b_id' => env('JWT_B_ID', ''),
        'api_key1' => env('JWT_API_KEY1', ''),
        'api_key2' => env('JWT_API_KEY2', ''),
    ],
];
