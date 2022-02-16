<?php

namespace App\Main\Api\Request;

use App\Main\AuthJWT\TokenJWT;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class RequestAbstract
{
    protected string $api_url;

    private array $headers = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];

    protected array $params = [];

    public function __construct(TokenJWT $tokenJWT, array $params = [])
    {
        $this->api_url = config('main.api.url');

        $this->params = $params;

        $this->headers['Cookie'] = sprintf(
            'WAVIOT_JWT=%s', $tokenJWT->getToken()
        );

        $this->token = $tokenJWT;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): RequestAbstract
    {
        $this->headers = $headers;
        return $this;
    }

    public function prepareParamsToQueryString(): string
    {
        $result = http_build_query($this->params);

        if ($result) {
            return '?' . $result;
        }

        return '';
    }

    public function get(string $path): array
    {
        return $this->response(Http::withHeaders($this->headers)->get($this->api_url . $path));
    }

    public function post(string $path): array
    {
        return $this->response(Http::withHeaders($this->headers)->post($this->api_url . $path, $this->params));
    }

    private function response(Response $response): array
    {
        if ($response->ok()) {
            return $response->json() ?? [];
        }
        return [];
    }

    abstract public function apply();
}
