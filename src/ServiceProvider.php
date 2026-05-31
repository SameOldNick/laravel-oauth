<?php

namespace SameOldNick\OAuth;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Laravel\Socialite\Contracts\Factory;
use SameOldNick\OAuth\Contracts\Handlers\OAuthCallbackHandler as OAuthCallbackHandlerContract;
use SameOldNick\OAuth\Contracts\Handlers\OAuthFlowHandler as OAuthFlowHandlerContract;
use SameOldNick\OAuth\Contracts\Handlers\OAuthRedirectHandler as OAuthRedirectHandlerContract;
use SameOldNick\OAuth\Contracts\Responses\AuthenticateResponse;
use SameOldNick\OAuth\Contracts\Responses\Errors as ErrorResponseContracts;
use SameOldNick\OAuth\Contracts\Responses\LoggedInResponse;
use SameOldNick\OAuth\Contracts\Services\OAuthAccountAssociator as OAuthAccountAssociatorContract;
use SameOldNick\OAuth\Contracts\Services\OAuthAuthenticationState as OAuthAuthenticationStateContract;
use SameOldNick\OAuth\Contracts\Services\OAuthGate as OAuthGateContract;
use SameOldNick\OAuth\Contracts\Services\OAuthUserRegistrar as OAuthUserRegistrarContract;
use SameOldNick\OAuth\Contracts\Services\OAuthUserResolver as OAuthUserResolverContract;
use SameOldNick\OAuth\Handlers\CallbackHandler;
use SameOldNick\OAuth\Handlers\OAuthFlowHandler;
use SameOldNick\OAuth\Handlers\RedirectHandler;
use SameOldNick\OAuth\Services\OAuthAccountAssociator;
use SameOldNick\OAuth\Services\OAuthAuthenticationState;
use SameOldNick\OAuth\Services\OAuthGate;
use SameOldNick\OAuth\Services\OAuthUserRegistrar;
use SameOldNick\OAuth\Services\OAuthUserResolver;
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
        $this->app->bind(OAuthUserRegistrarContract::class, OAuthUserRegistrar::class);
        $this->app->bind(OAuthAccountAssociatorContract::class, OAuthAccountAssociator::class);
        $this->app->bind(OAuthAuthenticationStateContract::class, OAuthAuthenticationState::class);
        $this->app->bind(OAuthUserResolverContract::class, OAuthUserResolver::class);
        $this->app->bind(OAuthGateContract::class, OAuthGate::class);

        $this->app->bind(ErrorResponseContracts\AlreadyLinkedErrorResponse::class, Responses\Errors\AlreadyLinkedErrorResponse::class);
        $this->app->bind(ErrorResponseContracts\RegistrationNotAllowedResponse::class, Responses\Errors\RegistrationNotAllowedResponse::class);
        $this->app->bind(ErrorResponseContracts\CannotLinkResponse::class, Responses\Errors\CannotLinkResponse::class);
        $this->app->bind(ErrorResponseContracts\LoginNotAllowedResponse::class, Responses\Errors\LoginNotAllowedResponse::class);
        $this->app->bind(ErrorResponseContracts\MustLoginToLinkResponse::class, Responses\Errors\MustLoginToLinkResponse::class);
        $this->app->bind(ErrorResponseContracts\UserTrashedResponse::class, Responses\Errors\UserTrashedResponse::class);
        $this->app->bind(AuthenticateResponse::class, Responses\AuthenticateResponse::class);
        $this->app->bind(LoggedInResponse::class, Responses\LoggedInResponse::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/oauth.php');

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
