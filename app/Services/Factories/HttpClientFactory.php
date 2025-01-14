<?php

namespace App\Services\Factories;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientFactory
{
    public static function create(): Client
    {
        return new Client([
            'handler' => self::createHandlerStack(),
            'verify' => false,
            'timeout' => config('news.http.timeout', 30),
            'connect_timeout' => config('news.http.connect_timeout', 10),
            'http_errors' => false,
            'headers' => self::getDefaultHeaders()
        ]);
    }

    private static function createHandlerStack(): HandlerStack
    {
        $stack = HandlerStack::create();
        $stack->push(self::createLoggingMiddleware());
        return $stack;
    }

    private static function createLoggingMiddleware()
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                Log::info('Outgoing request', ['uri' => (string) $request->getUri()]);
                
                return $handler($request, $options)->then(
                    function (ResponseInterface $response) {
                        Log::info('Incoming response', ['status' => $response->getStatusCode()]);
                        return $response;
                    }
                );
            };
        };
    }

    private static function getDefaultHeaders(): array
    {
        return [
            'User-Agent' => config('news.http.user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
        ];
    }
} 