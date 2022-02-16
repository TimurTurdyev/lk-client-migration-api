<?php

namespace App\Main\AuthJWT;

final class AdminJWT
{
    /**
     * Просто получаем админский JWT с ключами
     * для полного доступа к api.???.ru
     * @return string
     */
    public static function getToken(): string
    {
        $apikey = null;
        $ts = time();
        $jwtHeader = base64_encode('{"alg":"md5","typ":"WAVIOT_JWT"}');

        $jwtPayload = [
            'iss' => config('main.jwt.iss'),
            'aud' => config('main.jwt.aud'),
            'exp' => $ts + 3600,

            'firstName' => config('main.jwt.firstname'),
            'lastName' => config('main.jwt.lastname'),
            'middleName' => config('main.jwt.middleName'),

            'headEmail' => config('main.jwt.headEmail'),
            'account_id' => config('main.jwt.account_id'),
            'lk_id' => config('main.jwt.lk_id'),
            'b_id' => config('main.jwt.b_id'),
            'claims' => [
                'auth' => ['admin' => 'admin'],
                'b' => ['admin' => 'admin'],
                'driver-electro5' => ['admin' => 'admin'],
                'hes' => ['admin' => 'admin']
            ],
            'apiKeys' => [
                config('main.jwt.api_key1'),
                config('main.jwt.api_key2')
            ]
        ];

        if (!empty($apikey)) {
            $jwtPayload['apiKeys'] = $apikey;
        }

        $JWT = "$jwtHeader." . base64_encode(json_encode($jwtPayload, JSON_UNESCAPED_UNICODE));
        return "$JWT." . base64_encode(hash_hmac('md5', $JWT, config('main.jwt.salt_jwt')));
    }
}
