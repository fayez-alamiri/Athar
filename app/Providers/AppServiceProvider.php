<?php

namespace App\Providers;

use App\Models\Quote;
use App\Policies\QuotePolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Quote::class, QuotePolicy::class);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Log genuinely slow database queries (> 500ms) to stderr without exposing credentials or bindings
        DB::whenQueryingForLongerThan(500, function ($connection, $event) {
            Log::warning('Slow DB query detected', [
                'connection' => $connection->getName(),
                'time_ms' => $event->time,
                'sql' => $event->sql,
            ]);
        });
    }
}