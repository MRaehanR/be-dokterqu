<?php

namespace App\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('error', function (string $message, int $code) {
            return Response::json([
                'status' => false,
                'code' => $code,
                'message' => $message,
                'data' => [],
            ], $code);
        });

        Response::macro('success', function (string $message, int $code, $data) {
            return Response::json([
                'status' => true,
                'code' => $code,
                'message' => $message,
                'data' => $data,
            ], $code);
        });
    }
}
