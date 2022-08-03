<?php

namespace App\Providers;

use App\Route\ControllerRouter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class RouteControllerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerMacro();
    }

    protected function registerMacro()
    {
        Route::macro('controller', function ($uri, $controller) {
            $fullControllerName = $controller;
            if (! empty($this->groupStack)) {
                $group = end($this->groupStack);
                $fullControllerName = (isset($group['namespace']) && strpos($fullControllerName, 'App\Http\Controllers') === false && strpos($controller, '\\') !== 0) ? $group['namespace'].'\\'.$controller : $controller;
                $prefix = Str::slug($group['prefix'] ?? '');
            }
            $cr = new ControllerRouter();
            $routable = $cr->listRoutableActionFromController($fullControllerName);

            foreach ($routable as $uses => $potentialRoute) {
                $action = ['uses' => $uses];
                $action['as'] = $potentialRoute['name'];
                $potentialRoute['uri'] = $cr->removeRequestParam($potentialRoute['uri']);
                $potentialRoute['uri'] = preg_replace('{/$}', '', $potentialRoute['uri']);

                Route::{$potentialRoute['verb']}($uri.$potentialRoute['uri'], $action);
            }
        });

        Route::macro('controllers', function ($controllers) {
            if (count($controllers) > 0) {
                foreach ($controllers as $uri => $controller) {
                    Route::controller($uri, $controller);
                }
            }
        });
    }
}
