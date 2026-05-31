<?php

namespace SameOldNick\OAuth\Tests\Unit;

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Services\OAuthAuthenticationState;
use SameOldNick\OAuth\Contracts\Services\OAuthUserResolver;
use SameOldNick\OAuth\Tests\TestCase;
use Workbench\App\Models\User;

class OAuthAuthenticationServicesTest extends TestCase
{
    public function test_authentication_state_service_reports_logged_in_user(): void
    {
        $service = app(OAuthAuthenticationState::class);

        Auth::logout();
        $this->assertFalse($service->isLoggedIn());

        $user = User::factory()->create();
        Auth::login($user);

        $this->assertTrue($service->isLoggedIn());
        $this->assertSame((string) $user->getAuthIdentifier(), (string) $service->currentUser()->getAuthIdentifier());
    }

    public function test_linked_user_resolver_returns_linked_user_from_connected_account_model(): void
    {
        config()->set('oauth.models.connected_account.model', FakeConnectedAccountModel::class);

        $linkedUser = User::factory()->make();
        FakeConnectedAccountModel::$records = [
            [
                'provider_id' => 'provider-123',
                'provider_name' => 'github',
                'user' => $linkedUser,
            ],
        ];

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('clientName')->andReturn('github');

        $socialUser = new SocialiteUser;
        $socialUser->id = 'provider-123';

        $resolver = app(OAuthUserResolver::class);

        $resolvedUser = $resolver->resolveLinkedUser($client, $socialUser);

        $this->assertSame($linkedUser, $resolvedUser);
    }
}

class FakeConnectedAccountModel
{
    /**
     * @var list<array{provider_id:string, provider_name:string, user:mixed}>
     */
    public static array $records = [];

    public static function where(string $field, mixed $value): FakeConnectedAccountQuery
    {
        return (new FakeConnectedAccountQuery)->where($field, $value);
    }
}

class FakeConnectedAccountQuery
{
    /**
     * @var array<string, mixed>
     */
    private array $conditions = [];

    public function where(string $field, mixed $value): self
    {
        $this->conditions[$field] = $value;

        return $this;
    }

    public function first(): ?object
    {
        foreach (FakeConnectedAccountModel::$records as $record) {
            if (
                ($record['provider_id'] ?? null) === ($this->conditions['provider_id'] ?? null)
                && ($record['provider_name'] ?? null) === ($this->conditions['provider_name'] ?? null)
            ) {
                return (object) ['user' => $record['user'] ?? null];
            }
        }

        return null;
    }
}
