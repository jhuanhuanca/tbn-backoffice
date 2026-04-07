<?php

namespace App\Providers;

use App\Events\OrderCompleted;
use App\Listeners\QueueMlmProcessingOnOrderCompleted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
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
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Event::listen(OrderCompleted::class, QueueMlmProcessingOnOrderCompleted::class);
    }
}
