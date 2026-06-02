<?php

namespace SameOldNick\OAuth\Handlers\Pipeline\Steps;

use SameOldNick\OAuth\Concerns\CreatesConnectedAccountResponses;
use SameOldNick\OAuth\Contracts\Handlers\OAuthCallbackPipelineStep;
use SameOldNick\OAuth\Contracts\Services\OAuthGate;
use SameOldNick\OAuth\Contracts\Services\OAuthUserRegistrar;
use SameOldNick\OAuth\Contracts\Services\OAuthUserResolver;
use SameOldNick\OAuth\Enums\OAuthError;
use SameOldNick\OAuth\Handlers\Pipeline\OAuthCallbackPipelineContext;

class RegisterUserStep implements OAuthCallbackPipelineStep
{
    use CreatesConnectedAccountResponses;

    public function __construct(
        protected readonly OAuthGate $gate,
        protected readonly OAuthUserRegistrar $userRegistrar,
        protected readonly OAuthUserResolver $userResolver,
    ) {
        //
    }

    public function __invoke(OAuthCallbackPipelineContext $context)
    {
        $user = $this->userResolver->resolveExistingUserByEmail($context->getClient(), $context->getSocialUser());

        if (! $user || ! $user->exists) {
            // No existing user with that email - we can try to register a new user
            if (! $this->gate->canRegister($context->getClient(), $context->getSocialUser())) {
                // Registration not allowed by gate
                return $this->createErrorResponse(OAuthError::RegistrationNotAllowed, $context->getClient(), $context->getSocialUser());
            }

            $user = $this->userRegistrar->register($context->getClient(), $context->getSocialUser());

        }

        $context->setUser($user);
    }
}
