<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Rule;
use App\Observers\RuleObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Rule::observe(RuleObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
