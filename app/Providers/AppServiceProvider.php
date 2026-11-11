<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        // daisyUI-styled pagination (resources/views/vendor/pagination/daisy.blade.php)
        Paginator::defaultView('vendor.pagination.daisy');
    }
}
