<?php

namespace SameOldNick\OAuth\Testing;

use App\Models\User;
use SameOldNick\OAuth\Contracts\Services\OAuthAccountAssociator;
use SameOldNick\OAuth\Contracts\Services\OAuthGate;
use SameOldNick\OAuth\Contracts\Services\OAuthUserResolver;
use SameOldNick\OAuth\Facades\OAuth;
use Illuminate\Http\Response;
use Illuminate\Testing\TestCase;
use Illuminate\Testing\TestResponse;
use Laravel\Socialite\Contracts\User as SocialUser;
use Mockery\MockInterface;

/**
 * Trait providing OAuth testing utilities and assertions.
 *
 * This trait enables OAuth tests to:
 * - Mock the OAuth gate with custom authorization rules
 * - Create mock OAuth response contracts for testing different response scenarios
 * - Assert OAuth responses are of expected types and are actual instances
 * - Connect/link OAuth accounts to users for testing purposes
 * - Resolve and assert OAuth account associations
 *
 * Designed for use in feature tests of OAuth flows including registration,
 * linking, unlinking, and authentication scenarios.
 */
trait InteractsWithOAuthCallbacks
{
    /**
     * Set up the trait by configuring a default OAuth client for testing.
     */
    protected function setUpInteractsWithOAuthCallbacks(): void
    {
        // Ensure the default OAuth client is configured for tests
        config([
            'oauth.clients.github.enabled' => true,
            'oauth.clients.github.client_id' => 'test-client-id',
            'oauth.clients.github.client_secret' => 'test-client-secret',
        ]);
    }

    /**
     * Mock the OAuthGate to return specific values for its methods.
     *
     * @param  null|bool|\Closure  $canRegister  The value or callback to return for the canRegister method.
     * @param  null|bool|\Closure  $canLink  The value or callback to return for the canLink method.
     * @param  null|bool|\Closure  $canLogin  The value or callback to return for the canLogin method.
     * @param  null|bool|\Closure  $canUnlink  The value or callback to return for the canUnlink method.
     * @return TestCase
     */
    protected function mockOAuthGate(
        null|bool|\Closure $canRegister = null,
        null|bool|\Closure $canLink = null,
        null|bool|\Closure $canLogin = null,
        null|bool|\Closure $canUnlink = null,
    ) {
        return $this->mock(OAuthGate::class, function (MockInterface $mock) use ($canRegister, $canLink, $canLogin, $canUnlink) {
            if ($canRegister !== null) {
                $mock
                    ->shouldReceive('canRegister')
                    ->once()
                    ->andReturnUsing(function (...$args) use ($canRegister) {
                        return value($canRegister, ...$args);
                    });
            }

            if ($canLink !== null) {
                $mock
                    ->shouldReceive('canLink')
                    ->once()
                    ->andReturnUsing(function (...$args) use ($canLink) {
                        return value($canLink, ...$args);
                    });
            }

            if ($canLogin !== null) {
                $mock
                    ->shouldReceive('canLogin')
                    ->once()
                    ->andReturnUsing(function (...$args) use ($canLogin) {
                        return value($canLogin, ...$args);
                    });
            }

            if ($canUnlink !== null) {
                $mock
                    ->shouldReceive('canUnlink')
                    ->once()
                    ->andReturnUsing(function (...$args) use ($canUnlink) {
                        return value($canUnlink, ...$args);
                    });
            }
        });
    }

    /**
     * Helper method to create a mock for an OAuth response contract.
     *
     * @param  class-string<TResponse>  $responseClass  The class name of the OAuth response contract to mock.
     * @param  mixed  $return  The value or callback to return when the create method is called on the mock.
     * @param  null|\Closure(...mixed):bool  $withArgs  Optional callback to validate the arguments passed to the create method.
     * @return MockInterface
     */
    protected function createMockOAuthResponse(string $responseClass, $return, ?\Closure $withArgs = null)
    {
        $response = $this->mock($responseClass, function (MockInterface $mock) use ($withArgs, $return) {
            $expectation = $mock
                ->shouldReceive('create')
                ->once();

            if ($withArgs !== null) {
                $expectation->withArgs(function (...$args) use ($withArgs) {
                    return $withArgs(...$args) !== false;
                });
            }

            $expectation->andReturnUsing(function (...$args) use ($return) {
                return value($return, ...$args);
            });
        });

        return $response;
    }

    /**
     * Assert that a specific OAuth response class was returned during the callback handling.
     *
     * @param  class-string  $responseClass  The class name of the expected response.
     * @return TestCase
     */
    protected function assertOAuthResponseReturned(string $responseClass): static
    {
        $response = $this->createMockOAuthResponse($responseClass, app($responseClass)->create(...));

        $this->app->instance($responseClass, $response);

        return $this;
    }

    /**
     * Mock an OAuth response contract and force it to return a known response instance.
     *
     * This lets tests assert the callback result originated from the response contract
     * without relying on transport details such as redirects.
     *
     * @param  class-string  $responseClass
     * @param  null|\Closure(...mixed):bool  $withArgs
     */
    protected function expectOAuthResponseInstance(
        string $responseClass,
        ?\Closure $withArgs = null,
    ): TestOAuthResponse {
        $response = TestOAuthResponse::forOAuthResponseClass($responseClass);

        $mock = $this->createMockOAuthResponse($responseClass, $response, $withArgs);

        $this->app->instance($responseClass, $mock);

        return $response;
    }

    /**
     * Assert the framework returned the exact response instance produced by a response contract.
     */
    protected function assertOAuthResponseInstanceReturned(TestResponse $response, Response $expected): void
    {
        $this->assertSame(
            $expected,
            $response->baseResponse,
            'Expected callback response to be the same instance produced by the OAuth response contract.'
        );
    }

    /**
     * Helper method to associate a user with an OAuth account for testing purposes.
     *
     * @param  User  $user  The user to associate the OAuth account with.
     * @param  string  $clientName  The name of the OAuth client (e.g., 'github').
     * @param  SocialUser|null  $socialUser  Optional social user data to use for the association.
     */
    protected function connectOAuthAccount(User $user, string $clientName = 'github', ?SocialUser $socialUser = null): void
    {
        $client = OAuth::client($clientName);
        $socialUser = $socialUser ?? $client->provider()->user();

        app()->make(OAuthAccountAssociator::class)
            ->associate($client, $user, $socialUser);
    }

    /**
     * Resolve the user linked to an OAuth account.
     *
     * Uses the OAuthUserResolver service to find which user (if any) is associated
     * with the given OAuth provider and social user data.
     *
     * @param  string  $clientName  The OAuth client name (e.g., 'github').
     * @param  SocialUser|null  $socialUser  Optional social user data; defaults to mocked provider user.
     * @return User|null The associated user, or null if no account is linked.
     */
    protected function getOAuthLinkedUser(string $clientName = 'github', ?SocialUser $socialUser = null): ?User
    {
        $client = OAuth::client($clientName);
        $socialUser = $socialUser ?? $client->provider()->user();

        return app()->make(OAuthUserResolver::class)->resolveLinkedUser($client, $socialUser);
    }

    protected function assertOAuthAccountExists(string $clientName = 'github', ?SocialUser $socialUser = null): void
    {
        $linkedUser = $this->getOAuthLinkedUser($clientName, $socialUser);

        $this->assertNotNull($linkedUser, 'Expected an associated user but none was found.');
    }

    /**
     * Assert that an OAuth account is linked to a specific user.
     *
     * Verifies both that an OAuth account exists for the provider and that it is
     * associated with the correct user.
     *
     * @param  User  $user  The user expected to own the OAuth account.
     * @param  string  $clientName  The OAuth client name (e.g., 'github').
     * @param  SocialUser|null  $socialUser  Optional social user data; defaults to mocked provider user.
     */
    protected function assertOAuthAccountAssociated(User $user, string $clientName = 'github', ?SocialUser $socialUser = null): void
    {
        $linkedUser = $this->getOAuthLinkedUser($clientName, $socialUser);

        $this->assertNotNull($linkedUser, 'Expected an associated user but none was found.');
        $this->assertTrue(
            (string) $linkedUser->getAuthIdentifier() === (string) $user->getAuthIdentifier(),
            'The associated user does not match the expected user.'
        );
    }

    /**
     * Assert that an OAuth account is not linked to a specific user.
     *
     * Verifies either that no OAuth account exists for the provider, or that
     * it is associated with a different user.
     *
     * @param  User  $user  The user that should not own the OAuth account.
     * @param  string  $clientName  The OAuth client name (e.g., 'github').
     * @param  SocialUser|null  $socialUser  Optional social user data; defaults to mocked provider user.
     */
    protected function assertOAuthAccountNotAssociated(User $user, string $clientName = 'github', ?SocialUser $socialUser = null): void
    {
        $client = OAuth::client($clientName);
        $socialUser = $socialUser ?? $client->provider()->user();

        $linkedUser = app()->make(OAuthUserResolver::class)->resolveLinkedUser($client, $socialUser);

        $this->assertTrue(
            $linkedUser === null || (string) $linkedUser->getAuthIdentifier() !== (string) $user->getAuthIdentifier(),
            'Expected no associated user or an associated user that does not match the given user.'
        );
    }
}
