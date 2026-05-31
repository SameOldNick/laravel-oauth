<?php

namespace SameOldNick\OAuth\Handlers;

use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Handlers\OAuthCallbackHandler;
use SameOldNick\OAuth\Contracts\Responses\AuthenticateResponse;
use SameOldNick\OAuth\Contracts\Responses\Errors;
use SameOldNick\OAuth\Contracts\Responses\LoggedInResponse;
use SameOldNick\OAuth\Contracts\Services\OAuthAccountAssociator;
use SameOldNick\OAuth\Contracts\Services\OAuthAuthenticationState;
use SameOldNick\OAuth\Contracts\Services\OAuthGate;
use SameOldNick\OAuth\Contracts\Services\OAuthUserRegistrar;
use SameOldNick\OAuth\Contracts\Services\OAuthUserResolver;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\User as SocialUser;

class CallbackHandler implements OAuthCallbackHandler
{
    /**
     * Initializes handler dependencies.
     */
    public function __construct(
        protected readonly OAuthGate $gate,
        protected readonly OAuthUserRegistrar $userRegistrar,
        protected readonly OAuthAccountAssociator $accountAssociator,
        protected readonly OAuthAuthenticationState $authenticationState,
        protected readonly OAuthUserResolver $userResolver,
    ) {
        //
    }

    /**
     * Handles OAuth callback and dispatches to the correct scenario handler.
     */
    public function handleCallback(Client $client, SocialUser $socialUser)
    {
        $loggedIn = $this->authenticationState->isLoggedIn();

        if ($linkedUser = $this->userResolver->resolveLinkedUser($client, $socialUser)) {
            return $this->handleLinkedOAuth($client, $linkedUser, $socialUser, $loggedIn);
        }

        return $this->handleUnlinkedOAuth($client, $socialUser, $loggedIn);
    }

    /**
     * Handles the scenario where the OAuth account is already linked to a user.
     */
    private function handleLinkedOAuth(Client $client, Authenticatable $linkedUser, SocialUser $socialUser, bool $loggedIn)
    {
        if ($loggedIn) {
            $currentUser = $this->authenticationState->currentUser();

            /*
             * This handles the case where a logged-in user is trying to link an OAuth account that's already linked to their profile.
             * In this case, we allow the user to "re-link" the account, which updates the association with the new provider details (e.g. updated token).
             */

            if (! $this->gate->canLink($client, $socialUser, $currentUser)) {
                // Linking not allowed by gate
                return $this->cannotLinkResponse($client, $socialUser, $currentUser);
            }

            // User is linking an account that's already linked to their profile - just update the association
            $this->accountAssociator->associate($client, $linkedUser, $socialUser, true);

            return $this->loggedInResponse($client, $socialUser, $linkedUser);
        }

        /*
         * User is not logged in but the OAuth account is already linked to a user - log them in
         */

        if (! $this->gate->canLogin($client, $socialUser, $linkedUser)) {
            // Login not allowed by gate
            return $this->loginNotAllowedResponse($client, $socialUser, $linkedUser);
        }

        return $this->authenticateResponse($client, $socialUser, $linkedUser);
    }

    /**
     * Handles the scenario where the OAuth account is not yet linked to any user.
     */
    private function handleUnlinkedOAuth(Client $client, SocialUser $socialUser, bool $loggedIn)
    {
        if (! $loggedIn) {
            /*
             * User is not logged in and the OAuth account is not linked to any user - we may need to register a new user or ask them to log in to link
             */

            $user = $this->userResolver->resolveExistingUserByEmail($client, $socialUser);

            if (! $user) {
                // No existing user with that email - we can try to register a new user
                if (! $this->gate->canRegister($client, $socialUser)) {
                    // Registration not allowed by gate
                    return $this->registrationNotAllowedResponse($client, $socialUser);
                }

                $user = $this->userRegistrar->register($socialUser);
            }

            if (! $this->gate->canLink($client, $socialUser, $user)) {
                // Linking not allowed by gate
                return $this->cannotLinkResponse($client, $socialUser, $user);
            }

            // Link the new user to the OAuth account
            $this->accountAssociator->associate($client, $user, $socialUser);

            if (! $this->gate->canLogin($client, $socialUser, $user)) {
                // Login not allowed by gate
                return $this->loginNotAllowedResponse($client, $socialUser, $user);
            }

            return $this->authenticateResponse($client, $socialUser, $user);
        }

        /*
         * User is logged in but the OAuth account is not linked to any user - link to current user
         */

        // We don't allow linking to a different account than the one currently logged in, but we do allow "re-linking" (linking an account that's already linked to the same user) to update the provider details
        $user = $this->authenticationState->currentUser();

        if (! $this->gate->canLink($client, $socialUser, $user)) {
            // Linking not allowed by gate
            return $this->cannotLinkResponse($client, $socialUser, $user);
        }

        $this->accountAssociator->associate($client, $user, $socialUser, true);

        return $this->loggedInResponse($client, $socialUser, $user);
    }

    /**
     * Authenticate response
     *
     * @return void
     */
    protected function authenticateResponse(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        $response = app(AuthenticateResponse::class)->create($client, $socialUser, $user);

        return $response;
    }

    /**
     * Response when user is logged in
     */
    protected function loggedInResponse(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        $response = app(LoggedInResponse::class)->create($client, $socialUser, $user);

        return $response;
    }

    protected function registrationNotAllowedResponse(Client $client, SocialUser $socialUser)
    {
        $response = app(Errors\RegistrationNotAllowedResponse::class)->create($client, $socialUser);

        return $response;
    }

    protected function cannotLinkResponse(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        $response = app(Errors\CannotLinkResponse::class)->create($client, $socialUser, $user);

        return $response;
    }

    protected function loginNotAllowedResponse(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        $response = app(Errors\LoginNotAllowedResponse::class)->create($client, $socialUser, $user);

        return $response;
    }
}
