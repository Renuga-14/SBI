<?php

namespace App\Providers;

use App\Models\Client;
use App\Repositories\CommonRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CommonRepository::class, function ($app) {
            return new CommonRepository(new Client());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }



}
