<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register authorization gates/policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Pageant::class, \App\Policies\PageantPolicy::class);
    }
}
