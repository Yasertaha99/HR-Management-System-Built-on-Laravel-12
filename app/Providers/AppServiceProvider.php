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
        $this->app->bind(
            \App\Contracts\Attendance\WorkingHourRoundingStrategyInterface::class,
            \App\Strategies\Attendance\HalfHourRoundingStrategy::class
        );

        $this->app->bind(
            \App\Contracts\Telegram\TelegramClientInterface::class,
            \App\Services\Telegram\TelegramBotClient::class
        );

        $this->app->singleton(\App\Services\Notifications\NotificationDispatcher::class, function ($app) {
            $dispatcher = new \App\Services\Notifications\NotificationDispatcher();
            $dispatcher->registerChannel(new \App\Services\Notifications\Channels\DatabaseNotificationChannel());
            $dispatcher->registerChannel(new \App\Services\Telegram\TelegramNotificationChannel($app->make(\App\Contracts\Telegram\TelegramClientInterface::class)));
            return $dispatcher;
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
