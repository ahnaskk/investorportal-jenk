<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\SecureHeaders::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            //remove it  because redirecting from root            \App\Http\Middleware\HttpsProtocol::class,
            //\RenatoMarinho\LaravelPageSpeed\Middleware\InlineCss::class,
            //\RenatoMarinho\LaravelPageSpeed\Middleware\ElideAttributes::class,
            //\RenatoMarinho\LaravelPageSpeed\Middleware\InsertDNSPrefetch::class,
            //\RenatoMarinho\LaravelPageSpeed\Middleware\RemoveComments::class,
            //\RenatoMarinho\LaravelPageSpeed\Middleware\TrimUrls::class,
            \Laravel\Passport\Http\Middleware\CreateFreshApiToken::class,
            \Mazedlx\FeaturePolicy\AddFeaturePolicyHeaders::class,

        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            '\App\Http\Middleware\ApiDataLogger::class',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'           => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic'     => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'       => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can'            => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'          => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle'       => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'admin'          => \App\Http\Middleware\Admin::class,
        'investor'       => \App\Http\Middleware\Investor::class,
        'branch_manager' => \App\Http\Middleware\BranchManager::class,
        'viewer'          => \App\Http\Middleware\Viewer::class,
        'basicAuth' => \App\Http\Middleware\BasicAuth::class,
        'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
        'auth_permit'        => \App\Http\Middleware\PermissionMiddleware::class,
        'auth_allow'        => \App\Http\Middleware\IsValid::class,
        'whitelist'        => \App\Http\Middleware\FirewallMiddleware::class,
        'auth-download-api' => \App\Http\Middleware\AuthDownloadApiMiddleware::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,

    ];
}
