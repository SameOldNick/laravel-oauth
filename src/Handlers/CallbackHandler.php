<?php

namespace SameOldNick\OAuth\Handlers;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Concerns\CreatesConnectedAccountResponses;
use SameOldNick\OAuth\Contracts\Handlers\OAuthCallbackHandler;
use SameOldNick\OAuth\Contracts\Services\OAuthAccountAssociator;
use SameOldNick\OAuth\Contracts\Services\OAuthAuthenticationState;
use SameOldNick\OAuth\Contracts\Services\OAuthGate;
use SameOldNick\OAuth\Contracts\Services\OAuthUserRegistrar;
use SameOldNick\OAuth\Contracts\Services\OAuthUserResolver;
use SameOldNick\OAuth\Handlers\Pipeline\OAuthCallbackPipeline;
use SameOldNick\OAuth\Handlers\Pipeline\OAuthCallbackPipelineContext;
use SameOldNick\OAuth\Handlers\Pipeline\Steps\AssociateAccountStep;
use SameOldNick\OAuth\Handlers\Pipeline\Steps\CheckCanLinkStep;
use SameOldNick\OAuth\Handlers\Pipeline\Steps\CheckCanLoginStep;
use SameOldNick\OAuth\Handlers\Pipeline\Steps\CreateAuthenticateResponseStep;
use SameOldNick\OAuth\Handlers\Pipeline\Steps\CreateLoggedInResponseStep;
use SameOldNick\OAuth\Handlers\Pipeline\Steps\RegisterUserStep;

class CallbackHandler implements OAuthCallbackHandler
{
    use CreatesConnectedAccountResponses;

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
            return $this->handleLinkedOAuthWhenLoggedIn($client, $linkedUser, $socialUser);
        }

        return $this->handleLinkedOAuthWhenGuest($client, $linkedUser, $socialUser);
    }

    /**
     * Pipeline for linked OAuth callback when a user is already logged in.
     */
    private function handleLinkedOAuthWhenLoggedIn(Client $client, Authenticatable $linkedUser, SocialUser $socialUser)
    {
        $context = $this->createPipelineContext(
            $client,
            $socialUser,
            user: $linkedUser,
            currentUser: $this->authenticationState->currentUser(),
        );

        return $this->createPipeline($context)
            ->through([
                /*
                * This handles the case where a logged-in user is trying to link an OAuth account that's already linked to their profile.
                * In this case, we allow the user to "re-link" the account, which updates the association with the new provider details (e.g. updated token).
                */
                CheckCanLinkStep::class,
                // User is linking an account that's already linked to their profile - just update the association
                AssociateAccountStep::class,
                CreateLoggedInResponseStep::class,
            ])
            ->run();
    }

    /**
     * Pipeline for linked OAuth callback when no user is logged in.
     */
    private function handleLinkedOAuthWhenGuest(Client $client, Authenticatable $linkedUser, SocialUser $socialUser)
    {
        $context = $this->createPipelineContext(
            $client,
            $socialUser,
            user: $linkedUser,
        );

        return $this->createPipeline($context)
            ->through([
                /*
                 * User is not logged in but the OAuth account is already linked to a user - log them in.
                 */
                CheckCanLoginStep::class,
                CreateAuthenticateResponseStep::class,
            ])
            ->run();
    }

    /**
     * Handles the scenario where the OAuth account is not yet linked to any user.
     */
    private function handleUnlinkedOAuth(Client $client, SocialUser $socialUser, bool $loggedIn)
    {
        if (! $loggedIn) {
            return $this->handleUnlinkedOAuthWhenGuest($client, $socialUser);
        }

        return $this->handleUnlinkedOAuthWhenLoggedIn($client, $socialUser);
    }

    /**
     * Pipeline for unlinked OAuth callback when no user is logged in.
     */
    private function handleUnlinkedOAuthWhenGuest(Client $client, SocialUser $socialUser)
    {
        $context = $this->createPipelineContext(
            $client,
            $socialUser,
        );

        return $this->createPipeline($context)
            ->through([
                /*
                 * User is not logged in and the OAuth account is not linked to any user - we may need to register a new user or ask them to log in to link.
                 */
                RegisterUserStep::class,
                CheckCanLinkStep::class,
                AssociateAccountStep::class,
                CheckCanLoginStep::class,
                CreateAuthenticateResponseStep::class,
            ])
            ->run();
    }

    /**
     * Pipeline for unlinked OAuth callback when a user is already logged in.
     */
    private function handleUnlinkedOAuthWhenLoggedIn(Client $client, SocialUser $socialUser)
    {
        $context = $this->createPipelineContext(
            $client,
            $socialUser,
            currentUser: $this->authenticationState->currentUser(),
        );

        return $this->createPipeline($context)
            ->through([
                /*
                 * User is logged in but the OAuth account is not linked to any user - link to current user.
                 */
                // We don't allow linking to a different account than the one currently logged in, but we do allow "re-linking" (linking an account that's already linked to the same user) to update the provider details
                CheckCanLinkStep::class,
                AssociateAccountStep::class,
                CreateLoggedInResponseStep::class,
            ])
            ->run();
    }

    /**
     * Creates the pipeline context with necessary data for processing the OAuth callback.
     *
     * @param  Client  $client  The OAuth client being used
     * @param  SocialUser  $socialUser  The user information from the OAuth provider
     * @param  Authenticatable|null  $user  The user associated with the OAuth account (if any)
     * @param  Authenticatable|null  $currentUser  The currently authenticated user (if any)
     * @return OAuthCallbackPipelineContext The initialized pipeline context
     */
    private function createPipelineContext(Client $client, SocialUser $socialUser, ?Authenticatable $user = null, ?Authenticatable $currentUser = null): OAuthCallbackPipelineContext
    {
        return new OAuthCallbackPipelineContext(
            client: $client,
            socialUser: $socialUser,
            user: $user ?? $currentUser,
            currentUser: $currentUser,
        );
    }

    /**
     * Creates a new instance of the OAuth callback pipeline with the given context.
     *
     * @param  OAuthCallbackPipelineContext  $context  The context to be passed through the pipeline
     * @return OAuthCallbackPipeline The initialized pipeline ready to run
     */
    private function createPipeline(OAuthCallbackPipelineContext $context): OAuthCallbackPipeline
    {
        return new OAuthCallbackPipeline($context);
    }
}
