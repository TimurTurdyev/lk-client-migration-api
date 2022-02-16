<?php

namespace App\Main\Api;

use App\Main\Api\Request\ModemSetting;
use App\Main\AuthJWT\AdminJWT;
use App\Main\AuthJWT\TokenJWT;

class ApiClient
{
    private TokenJWT $tokenJWT;

    public function __construct()
    {
        $this->tokenJWT = new TokenJWT(AdminJWT::getToken());
    }

    public function modemSetting(array $params): array
    {
        $modemSetting = new ModemSetting($this->tokenJWT, $params);
        return $modemSetting->apply();
    }
}
