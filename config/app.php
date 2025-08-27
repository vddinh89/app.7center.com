<?php

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
$serverName = $_SERVER['SERVER_NAME'] ?? php_uname('n');
$appUrl = (!empty($serverName)) ? ($protocol . '://' . $serverName) : ($protocol . '://localhost');
$appKey = 'SomeRandomStringWith32Characters';

return [
	
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */
	
    'name' => 'SiteName',
	
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
    
    'env' => (function_exists('env')) ? env('APP_ENV', 'local') : 'local',
    
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
    
    'debug' => (function_exists('env')) ? env('APP_DEBUG', true) : true,
    
    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */
	
    'url' => (function_exists('env')) ? env('APP_URL', $appUrl) : $appUrl,
	
	'asset_url' => (function_exists('env')) ? env('ASSET_URL') : null,

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */
	
    'timezone' => (function_exists('env')) ? env('TIMEZONE', 'UTC') : 'UTC',
	
    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */
	
    'locale' => (function_exists('env')) ? env('APP_LOCALE', 'en') : 'en',
	
    'fallback_locale' => (function_exists('env')) ? env('APP_FALLBACK_LOCALE', 'en') : 'en',
	
	'faker_locale' => (function_exists('env')) ? env('APP_FAKER_LOCALE', 'en_US') : 'en_US',
	
    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',
	
    'key' => (function_exists('env')) ? env('APP_KEY', $appKey) : $appKey,
	
    'previous_keys' => [
	    ...array_filter(
		    explode(',', env('APP_PREVIOUS_KEYS', ''))
	    ),
    ],
	
	/*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */
    
    'maintenance' => [
	    'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
	    'store'  => env('APP_MAINTENANCE_STORE', 'database'),
    ],
	
];
