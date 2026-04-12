<?php

namespace App\Providers;

use App\Events\OrderCompleted;
use App\Listeners\QueueMlmProcessingOnOrderCompleted;
use App\Models\BinaryPlacement;
use App\Models\Withdrawal;
use App\Policies\BinaryPlacementPolicy;
use App\Policies\WithdrawalPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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

        Gate::policy(Withdrawal::class, WithdrawalPolicy::class);
        Gate::policy(BinaryPlacement::class, BinaryPlacementPolicy::class);

        Event::listen(OrderCompleted::class, QueueMlmProcessingOnOrderCompleted::class);
    }
}
