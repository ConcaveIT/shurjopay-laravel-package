<?php

namespace Dits\ShurjopayLaravelPackage\Http;

use Illuminate\Support\Facades\Http;

class Client
{
    protected int $timeout;

    public function __construct()
    {
        $this->timeout = (int) config('shurjopay.http_timeout', 15);
    }

    protected function http()
    {
        return Http::timeout($this->timeout);
    }

    public function postJson(string $path, array $body = [], array $headers = [])
    {
        $response = $this->http()->withHeaders($headers)->post($path, $body);
        return $this->decode($response);
    }

    public function postForm(string $path, array $form = [], array $headers = [])
    {
        $response = $this->http()->withHeaders($headers)->asMultipart()->post($path, $form);
        return $this->decode($response);
    }

    protected function decode($response)
    {
        if ($response->successful()) {
            return $response->json();
        }

        // return array with status and body for debugging
        return [
            'status' => $response->status(),
            'body' => $response->body(),
        ];
    }
}
