<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Payment;
use App\Observers\OrderObserver;
use App\Observers\PaymentObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        Order::observe(OrderObserver::class);
        Payment::observe(PaymentObserver::class);

        // Force HTTPS di production - gunakan APP_URL dari env
        $appUrl = config('app.url');

        if ($appUrl && str_starts_with($appUrl, 'https')) {
            URL::forceScheme('https');
        }
    }
}