<?php

namespace App\Providers;

use Illuminate\Http\Response as HttpResponse;
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

        Response::macro('error_server', function () {
            return Response::json([
                'status' => false,
                'code' => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Internal Server Error',
                'data' => [],
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        });

        Response::macro('error_unauthenticated', function () {
            return response()->json([
                'status' => false,
                'code' => HttpResponse::HTTP_UNAUTHORIZED,
                'message' => 'Unauthenticated',
                'data' => [],
            ], HttpResponse::HTTP_UNAUTHORIZED);
        });

        Response::macro('success', function (string $message, int $code, $data = []) {
            return Response::json([
                'status' => true,
                'code' => $code,
                'message' => $message,
                'data' => $data,
            ], $code);
        });
    }
}
