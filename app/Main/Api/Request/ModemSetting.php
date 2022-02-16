<?php

namespace App\Main\Api\Request;

class ModemSetting extends RequestAbstract
{
    public const PATH = 'setting';

    public function apply()
    {
        return $this->get(static::PATH . $this->prepareParamsToQueryString());
    }
}
