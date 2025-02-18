<?php

namespace App\Http;

use GuzzleHttp\Client;

class HttpClient
{
    private static ?Client $client = null;

    public static function getInstance(): Client
    {
        if (self::$client === null) {
            self::$client = new Client(['timeout' => 5.0]);
        }
        return self::$client;
    }
}
