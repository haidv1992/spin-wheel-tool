<?php

namespace Dbiz\SpinWheelTool;

use Dbiz\SpinWheelTool\Console\Commands\PublishSpinWheelTool;
use Dbiz\SpinWheelTool\Http\Middleware\Authorize;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Http\Middleware\Authenticate;
use Laravel\Nova\Nova;

class ToolServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->app->booted(function () {
			$this->routes();
		});
		
		
		$this->publishes([
			__DIR__.'/config/spinwheeltool.php' => config_path('spinwheeltool.php'),
		], 'config');
		
		
		// Tải migrations cho cấu hình bánh xe quay
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->publishes([
            __DIR__.'/Database/Migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../dist/frontend' => public_path('spin-wheel-tool'),
        ], 'assets');

        // Tải views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'spin-wheel-tool');

        // Đăng ký Blade component cho frontend
        Blade::component('spin-wheel', \Dbiz\SpinWheelTool\Components\SpinWheelComponent::class);
		
		if ($this->app->runningInConsole()) {
			$this->commands([
				PublishSpinWheelTool::class,
			]);
		}
	}

	protected function routes()
	{

		if ($this->app->routesAreCached()) {
			return;
		}

		// Route cho Inertia.js
		Nova::router(['nova', Authenticate::class, Authorize::class], 'spin-wheel-tool')
			->group(__DIR__.'/../routes/inertia.php');

		// Route cho các API backend
		Route::prefix('nova-vendor/spin-wheel-tool')
			->group(__DIR__.'/../routes/api.php');
	}

	public function register()
	{
		$this->mergeConfigFrom(
			__DIR__.'/config/spinwheeltool.php', 'spinwheeltool'
		);
		
		// Bind the controller class based on config
		$this->app->bind('spinwheeltool.controller', function ($app) {
			$controller = config('spinwheeltool.controller');
			
			if ($controller && class_exists($controller)) {
				return $controller; 
			}
			
			// Default controller class string
			return \Dbiz\SpinWheelTool\Http\Controllers\SpinWheelController::class;
		});
	}
}
