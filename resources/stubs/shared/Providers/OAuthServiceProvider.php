<?php

namespace VendorName\Providers;

use Illuminate\Support\ServiceProvider;
use SameOldNick\OAuth\Contracts\Responses\AuthenticateResponse as AuthenticateResponseContract;
use SameOldNick\OAuth\Contracts\Responses\Errors as ErrorResponseContracts;
use SameOldNick\OAuth\Contracts\Responses\LoggedInResponse as LoggedInResponseContract;
use SameOldNick\OAuth\Contracts\Services\OAuthAccountAssociator as OAuthAccountAssociatorContract;
use SameOldNick\OAuth\Contracts\Services\OAuthAuthenticationState as OAuthAuthenticationStateContract;
use SameOldNick\OAuth\Contracts\Services\OAuthGate as OAuthGateContract;
use SameOldNick\OAuth\Contracts\Services\OAuthUserRegistrar as OAuthUserRegistrarContract;
use SameOldNick\OAuth\Contracts\Services\OAuthUserResolver as OAuthUserResolverContract;
use VendorName\OAuth\Fortify\Responses\AuthenticateResponse;
use VendorName\OAuth\Fortify\Responses\Errors\AlreadyLinkedErrorResponse;
use VendorName\OAuth\Fortify\Responses\Errors\CannotLinkResponse;
use VendorName\OAuth\Fortify\Responses\Errors\LoginNotAllowedResponse;
use VendorName\OAuth\Fortify\Responses\Errors\MustLoginToLinkResponse;
use VendorName\OAuth\Fortify\Responses\Errors\RegistrationNotAllowedResponse;
use VendorName\OAuth\Fortify\Responses\Errors\UserTrashedResponse;
use VendorName\OAuth\Fortify\Responses\LoggedInResponse;
use VendorName\OAuth\Fortify\Services\OAuthAccountAssociator;
use VendorName\OAuth\Fortify\Services\OAuthAuthenticationState;
use VendorName\OAuth\Fortify\Services\OAuthGate;
use VendorName\OAuth\Fortify\Services\OAuthUserRegistrar;
use VendorName\OAuth\Fortify\Services\OAuthUserResolver;

class OAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OAuthUserRegistrarContract::class, OAuthUserRegistrar::class);
        $this->app->bind(OAuthAccountAssociatorContract::class, OAuthAccountAssociator::class);
        $this->app->bind(OAuthAuthenticationStateContract::class, OAuthAuthenticationState::class);
        $this->app->bind(OAuthUserResolverContract::class, OAuthUserResolver::class);
        $this->app->bind(OAuthGateContract::class, OAuthGate::class);

        $this->app->bind(ErrorResponseContracts\AlreadyLinkedErrorResponse::class, AlreadyLinkedErrorResponse::class);
        $this->app->bind(ErrorResponseContracts\RegistrationNotAllowedResponse::class, RegistrationNotAllowedResponse::class);
        $this->app->bind(ErrorResponseContracts\CannotLinkResponse::class, CannotLinkResponse::class);
        $this->app->bind(ErrorResponseContracts\LoginNotAllowedResponse::class, LoginNotAllowedResponse::class);
        $this->app->bind(ErrorResponseContracts\MustLoginToLinkResponse::class, MustLoginToLinkResponse::class);
        $this->app->bind(ErrorResponseContracts\UserTrashedResponse::class, UserTrashedResponse::class);
        $this->app->bind(AuthenticateResponseContract::class, AuthenticateResponse::class);
        $this->app->bind(LoggedInResponseContract::class, LoggedInResponse::class);
    }
}
