<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\ServiceProvider;

class ScrambleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Scramble::extendOpenApi(function (OpenApi $openApi) {
        //     // Add Bearer token security scheme
        //     $openApi->secure(
        //         SecurityScheme::http('bearer', 'sanctum')
        //             ->description('Laravel Sanctum authentication. Obtain token from /api/auth/login or /api/auth/register')
        //     );
        // });

        Scramble::configure()
        ->withDocumentTransformers(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer', 'sanctum')
                    ->setDescription('Laravel Sanctum token authentication. Obtain token from /api/auth/login or /api/auth/register')
            );
        });
        // Automatically mark routes with 'auth:sanctum' middleware as authenticated
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            // This ensures all protected routes show the lock icon
        });
    }
}