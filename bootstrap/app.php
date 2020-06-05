<?php

require_once __DIR__ . '/../vendor/autoload.php';

$classes = [
    'App' => Illuminate\Support\Facades\App::class,
    'Bus' => Illuminate\Support\Facades\Bus::class,
    'Crypt' => Illuminate\Support\Facades\Crypt::class,
    'Mail' => Illuminate\Support\Facades\Mail::class,
    'Request' => Illuminate\Support\Facades\Request::class,
];

foreach ($classes as $alias => $class) {
    if (!class_exists($alias)) {
        class_alias($class, $alias);
    }
}

try {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

// Create the service container
$app = new Laravel\Lumen\Application(
    realpath(__DIR__ . '/../')
);

// Load the configuration files
$app->configure('app');
$app->configure('auth');
$app->configure('broadcasting');
$app->configure('cache');
$app->configure('cors');
$app->configure('database');
$app->configure('logging');
$app->configure('queue');
$app->configure('validation');
$app->configure('view');

if ($app->environment('local')) {
    $app->configure('ide-helper');
}

$app->withFacades(true, [
    'Illuminate\Support\Facades\Hash' => 'Hash',
]);

$app->configure('mail');

$app->withEloquent();

// Register container bindings
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    \Illuminate\Contracts\Translation\Translator::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

// Register middleware
$app->middleware([
    \Barryvdh\Cors\HandleCors::class,
    App\Http\Middleware\TrimStrings::class,
    App\Http\Middleware\ConvertEmptyStringsToNull::class,
    App\Http\Middleware\SetLocale::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'cors' => \Barryvdh\Cors\HandleCors::class,
]);

// Register service providers
$app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Barryvdh\Cors\ServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(Dusterio\LumenPassport\PassportServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);

if ($app->environment('local')) {
    $app->register(Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
    $app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
}

// Load the application routes
$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/web.php';
});

$app->configure('services');

date_default_timezone_set('Europe/Berlin');

return $app;
