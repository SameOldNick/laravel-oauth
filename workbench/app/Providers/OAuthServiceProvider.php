<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use SameOldNick\OAuth\Contracts\Responses\AuthenticateResponse as AuthenticateResponseContract;
use SameOldNick\OAuth\Contracts\Responses\ErrorResponse as ErrorResponseContract;
use SameOldNick\OAuth\Contracts\Responses\ExceptionResponse as ExceptionResponseContract;
use SameOldNick\OAuth\Contracts\Responses\LoggedInResponse as LoggedInResponseContract;
use SameOldNick\OAuth\Contracts\Services\OAuthAccountAssociator as OAuthAccountAssociatorContract;
use SameOldNick\OAuth\Contracts\Services\OAuthAuthenticationState as OAuthAuthenticationStateContract;
use SameOldNick\OAuth\Contracts\Services\OAuthGate as OAuthGateContract;
use SameOldNick\OAuth\Contracts\Services\OAuthUserRegistrar as OAuthUserRegistrarContract;
use SameOldNick\OAuth\Contracts\Services\OAuthUserResolver as OAuthUserResolverContract;
use Workbench\App\OAuth\Fortify\Responses\AuthenticateResponse;
use Workbench\App\OAuth\Fortify\Responses\ErrorResponse;
use Workbench\App\OAuth\Fortify\Responses\ExceptionResponse;
use Workbench\App\OAuth\Fortify\Responses\LoggedInResponse;
use Workbench\App\OAuth\Fortify\Services\OAuthAccountAssociator;
use Workbench\App\OAuth\Fortify\Services\OAuthAuthenticationState;
use Workbench\App\OAuth\Fortify\Services\OAuthGate;
use Workbench\App\OAuth\Fortify\Services\OAuthUserRegistrar;
use Workbench\App\OAuth\Fortify\Services\OAuthUserResolver;

class OAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OAuthUserRegistrarContract::class, OAuthUserRegistrar::class);
        $this->app->bind(OAuthAccountAssociatorContract::class, OAuthAccountAssociator::class);
        $this->app->bind(OAuthAuthenticationStateContract::class, OAuthAuthenticationState::class);
        $this->app->bind(OAuthUserResolverContract::class, OAuthUserResolver::class);
        $this->app->bind(OAuthGateContract::class, OAuthGate::class);

        $this->app->bind(ErrorResponseContract::class, ErrorResponse::class);
        $this->app->bind(AuthenticateResponseContract::class, AuthenticateResponse::class);
        $this->app->bind(LoggedInResponseContract::class, LoggedInResponse::class);
        $this->app->bind(ExceptionResponseContract::class, ExceptionResponse::class);
    }
}
