<?php

return [
    /*
        onesignal
    */

        'onesignal_id' => env('INVESTOR_APP_ID'),
        'investor_app_id' => env('INVESTOR_APP_ID'),
        'channel_payment_id' => env('CHANNEL_PAYMENT_ID'),
        'channel_newdeal_id' => env('CHANNEL_NEWDEAL_ID'),
        'channel_document_id' => env('CHANNEL_DOCUMENT_ID'),
        'merchant_app_id' => env('MERCHANT_APP_ID'),
        'channel_offer_id'=>env('CHANNEL_OFFER_ID'),
        'merchant_payment_channel_id'=>env('MERCHANT_PAYMENT_CHANNEL_ID'),
        'merchant_app_rest_api_key'=>env('MERCHNAT_APP_REST_API_KEY'),
        'investor_app_rest_api_key'=>env('INVESTOR_APP_REST_API_KEY'),

    /*
    CRM API
    */
    'crm_user_name'=>env('crm_user_name'),
    'crm_password'=>env('crm_password'),
    'crm_url'=>rtrim(env('crm_url'), '/'),
    'send_permission'=>env('send_permission'),

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    'database' => env('DB_DATABASE'),
    'database2' => env('DB_DATABASE_SECOND'),

    'username'=> env('DB_USERNAME'),

    'password'=>  env('DB_PASSWORD'),

    'db_url'=>  env('DB_HOST'),

     'stripe_key'    => env('STRIPE_KEY'),
     'stripe_secret' => env('STRIPE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Application Version
    |--------------------------------------------------------------------------
    |
    | This value is the version of your application. This value is used when
    | the framework needs to place the application's version in a notification
    | or any other location as required by the application or its packages.
    */

    'version' => '2.8.0',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
       Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        \App\Services\IPVueTable\IPVuetableServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        OwenIt\Auditing\AuditingServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\TelescopeServiceProvider::class,
        App\Providers\HelperServiceProvider::class,
        App\Providers\UserActivityLogServiceProvider::class,
        App\Providers\PermissionLogServiceProvider::class,
        App\Providers\DashboardServiceProvider::class,
        App\Providers\RouteControllerServiceProvider::class,
        // App\Library\Repository\Interfaces\IUserRepository::class,

         App\Providers\RepoServiceProvider::class,
         App\Providers\SanctumServiceProvider::class,
         App\Providers\MerchantUtilityServiceProvider::class,

        // App\Providers\AppServiceProvider::class,
        // App\Providers\AuthServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class,
        // App\Providers\EventServiceProvider::class,
        OwenIt\Auditing\AuditingServiceProvider::class,
        // App\Providers\RouteServiceProvider::class,
        App\Providers\TelescopeServiceProvider::class,
        Spatie\Permission\PermissionServiceProvider::class,
        Yajra\DataTables\DataTablesServiceProvider::class,
        // Laravel\Dusk\DuskServiceProvider::class,
        // App\Providers\MerchantUtilityServiceProvider::class,
        // App\Providers\RepoServiceProvider::class,
        Barryvdh\DomPDF\ServiceProvider::class,
        Laravel\Passport\PassportServiceProvider::class,
        Maatwebsite\Excel\ExcelServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
//        qoraiche\mailEclipse\mailEclipseServiceProvider::class,
        App\Providers\FortifyServiceProvider::class,
        Jorenvh\Share\Providers\ShareServiceProvider::class,
        Shetabit\Visitor\Provider\VisitorServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'Date' => Illuminate\Support\Facades\Date::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
        'FFM' => App\Library\Facades\FieldFormatter::class,
        'MTB' => App\Library\Facades\MerchantTableBuilder::class,
        'GPH' => App\Library\Facades\GeneratePaymentHelper::class,

        'MarketOfferHelper' => App\Library\Facades\MarketOfferHelper::class,
        'CompanyHelper' => App\Library\Facades\CompanyHelper::class,
        'InvestorTransactionHelper' => App\Library\Facades\InvestorTransactionHelper::class,
        'DashboardHelper' => App\Library\Facades\DashboardHelper::class,
        'NotesHelper' => App\Library\Facades\NotesHelper::class,
        'BranchHelper' => App\Library\Facades\BranchHelper::class,
        'MerchantHelper' => App\Library\Facades\MerchantHelper::class,
        'PaymentTermHelper'=>App\Library\Facades\PaymentTermHelper::class,
        'InvestorAssignHelper'=>App\Library\Facades\InvestorAssignHelper::class,
        'MerchantUserHelper'=>App\Library\Facades\MerchantUserHelper::class,
        'ParticipantPaymentHelper'=>App\Library\Facades\ParticipantPaymentHelper::class,
        'LiquidityLogHelper' => App\Library\Facades\LiquidityLogHelper::class,
        'InvestorHelper' => App\Library\Facades\InvestorHelper::class,
        'LabelHelper' => App\Library\Facades\LabelHelper::class,
        'CollectionUserHelper' => App\Library\Facades\CollectionUserHelper::class,
        'CompanyHelper' => App\Library\Facades\CompanyHelper::class,
        'InvestorTransactionHelper' => App\Library\Facades\InvestorTransactionHelper::class,
        'DashboardHelper' => App\Library\Facades\DashboardHelper::class,
        'DynamicReportHelper'=>App\Library\Facades\DynamicReportHelper::class,
        'FundingHelper' => App\Library\Facades\FundingHelper::class,
        'MerchantStatementHelper' => App\Library\Facades\MerchantStatementHelper::class,
        'FaqHelper' => App\Library\Facades\FaqHelper::class,
        'SettingHelper' => App\Library\Facades\SettingHelper::class,
        'ReportHelper' => App\Library\Facades\ReportHelper::class,
        'BankHelper' => App\Library\Facades\BankHelper::class,
        'PayCalc' => App\Library\Facades\PaymentCalculator::class,
        'ITran' => App\Library\Facades\InvestorTransaction::class,
        'EventHistory' => App\Library\Facades\EventHistory::class,
        'Permissions' => App\Library\Facades\Permissions::class,
        'Excel' => Maatwebsite\Excel\Facades\Excel::class,
        'PDF' => Barryvdh\DomPDF\Facade::class,
        'Pusher'    =>  Pusher\Pusher::class,
        'Module' => Caffeinated\Modules\Facades\Module::class,
        'Backup' => Cornford\Backup\Facades\Backup::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Datatables' => yajra\Datatables\Datatables::class,
        'Logo' => App\Http\Controllers\HomeController::class,
        'IPVueTable' => \App\Services\IPVueTable\IPVuetableFacade::class,
        'Share' => Jorenvh\Share\ShareFacade::class,
        'TemplateHelper' => App\Library\Facades\TemplateHelper::class,
        'PaymentHelper' => App\Library\Facades\PaymentHelper::class,
        'CRMHelper' => App\Library\Facades\CRMHelper::class,
        'Visitor' => Shetabit\Visitor\Facade\Visitor::class,

    ],

];
