<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\CreatesNewUsers as CreatesNewUsersContract;
use Workbench\App\Actions\Fortify\CreateNewUser;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(CreatesNewUsersContract::class, CreateNewUser::class);

        $this->app->alias(CreatesNewUsersContract::class, \App\Actions\Fortify\CreateNewUser::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
