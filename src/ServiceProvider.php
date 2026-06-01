<?php

namespace SameOldNick\OAuth;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laravel\Socialite\Contracts\Factory;
use SameOldNick\OAuth\Commands\InstallOAuth;
use SameOldNick\OAuth\Contracts\Handlers\OAuthCallbackHandler as OAuthCallbackHandlerContract;
use SameOldNick\OAuth\Contracts\Handlers\OAuthFlowHandler as OAuthFlowHandlerContract;
use SameOldNick\OAuth\Contracts\Handlers\OAuthRedirectHandler as OAuthRedirectHandlerContract;
use SameOldNick\OAuth\Handlers\CallbackHandler;
use SameOldNick\OAuth\Handlers\OAuthFlowHandler;
use SameOldNick\OAuth\Handlers\RedirectHandler;
use SameOldNick\OAuth\Socialite\SocialiteManager;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(OAuth::class);
        $this->app->alias(OAuth::class, 'oauth');

        $this->app->extend(Factory::class, function (Factory $factory, Container $app) {
            return new SocialiteManager($app);
        });

        $this->app->bind(OAuthFlowHandlerContract::class, OAuthFlowHandler::class);
        $this->app->bind(OAuthRedirectHandlerContract::class, RedirectHandler::class);
        $this->app->bind(OAuthCallbackHandlerContract::class, CallbackHandler::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/oauth.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallOAuth::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/oauth.php' => config_path('oauth.php'),
        ], 'oauth-config');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'oauth-migrations');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'oauth');
        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/oauth'),
        ], 'oauth-translations');
    }
}
