<?php

namespace SameOldNick\OAuth\Tests\Feature;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\BitbucketProvider;
use Laravel\Socialite\Two\FacebookProvider;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\GitlabProvider;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\LinkedInProvider;
use Laravel\Socialite\Two\SlackProvider;
use Laravel\Socialite\Two\TwitchProvider;
use Laravel\Socialite\Two\TwitterProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use SameOldNick\OAuth\Clients\Bitbucket;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Clients\Facebook;
use SameOldNick\OAuth\Clients\GitHub;
use SameOldNick\OAuth\Clients\GitLab;
use SameOldNick\OAuth\Clients\Google;
use SameOldNick\OAuth\Clients\LinkedIn;
use SameOldNick\OAuth\Clients\Slack;
use SameOldNick\OAuth\Clients\Twitch;
use SameOldNick\OAuth\Clients\Twitter;
use SameOldNick\OAuth\Facades\OAuth;
use SameOldNick\OAuth\Models\OAuthProvider;
use SameOldNick\OAuth\Tests\TestCase;

/**
 * Tests that every bundled OAuth client is wired up to a working Socialite driver.
 */
class OAuthProvidersTest extends TestCase
{
    /**
     * @return array<string, array{name: string, client: class-string<Client>, provider: class-string<AbstractProvider>, authorizeHost: string}>
     */
    public static function providers(): array
    {
        $drivers = [
            'bitbucket' => [Bitbucket::class, BitbucketProvider::class, 'bitbucket.org'],
            'facebook' => [Facebook::class, FacebookProvider::class, 'www.facebook.com'],
            'github' => [GitHub::class, GithubProvider::class, 'github.com'],
            'gitlab' => [GitLab::class, GitlabProvider::class, 'gitlab.com'],
            'google' => [Google::class, GoogleProvider::class, 'accounts.google.com'],
            'linkedin' => [LinkedIn::class, LinkedInProvider::class, 'www.linkedin.com'],
            'slack' => [Slack::class, SlackProvider::class, 'slack.com'],
            'twitch' => [Twitch::class, TwitchProvider::class, 'id.twitch.tv'],
            'twitter' => [Twitter::class, TwitterProvider::class, 'twitter.com'],
        ];

        $providers = [];

        foreach ($drivers as $name => [$client, $provider, $authorizeHost]) {
            $providers[$name] = [
                'name' => $name,
                'client' => $client,
                'provider' => $provider,
                'authorizeHost' => $authorizeHost,
            ];
        }

        return $providers;
    }

    public function test_all_providers_are_registered_as_drivers(): void
    {
        $this->assertEqualsCanonicalizing(
            ['bitbucket', 'facebook', 'github', 'gitlab', 'google', 'linkedin', 'slack', 'twitch', 'twitter'],
            OAuth::getAllDriverNames(),
        );
    }

    public function test_configured_providers_only_include_providers_with_credentials(): void
    {
        $this->configureProvider('github');

        $this->assertSame(['github'], array_values(OAuth::getConfiguredClientNames()));
    }

    #[DataProvider('providers')]
    public function test_client_resolves_the_matching_socialite_provider(string $name, string $client, string $provider, string $authorizeHost): void
    {
        $this->configureProvider($name);

        $oauthClient = OAuth::client($name);

        $this->assertInstanceOf($client, $oauthClient);
        $this->assertSame($name, $oauthClient->clientName());
        $this->assertTrue($oauthClient->isConfigured());
        $this->assertInstanceOf($provider, $oauthClient->provider());
    }

    #[DataProvider('providers')]
    public function test_redirect_route_sends_user_to_the_provider(string $name, string $client, string $provider, string $authorizeHost): void
    {
        $this->configureProvider($name);

        $response = $this->get(route('oauth.redirect', ['client' => $name]));

        $response->assertRedirect();

        $location = $response->headers->get('Location');

        $this->assertSame($authorizeHost, parse_url($location, PHP_URL_HOST));

        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        $this->assertSame('test-client-id', $query['client_id'] ?? null);
        $this->assertSame(route('oauth.callback', ['client' => $name]), $query['redirect_uri'] ?? null);
    }

    #[DataProvider('providers')]
    public function test_callback_registers_user_and_connects_the_provider(string $name, string $client, string $provider, string $authorizeHost): void
    {
        $this->configureProvider($name);

        OAuth::fake($name, ['email' => "user@{$name}.test"]);

        $this->get(route('oauth.callback', ['client' => $name]))->assertRedirect();

        $this->assertAuthenticated();
        $this->assertSame("user@{$name}.test", auth()->user()?->email);

        $this->assertTrue(
            OAuthProvider::query()
                ->where('provider_name', $name)
                ->where('provider_id', '1234567890')
                ->exists(),
            "Expected a connected account to be stored for the [{$name}] provider.",
        );
    }

    private function configureProvider(string $name): void
    {
        config([
            "oauth.clients.{$name}.enabled" => true,
            "oauth.clients.{$name}.client_id" => 'test-client-id',
            "oauth.clients.{$name}.client_secret" => 'test-client-secret',
            "oauth.clients.{$name}.redirect" => "https://example.com/oauth/callback/{$name}",
        ]);
    }
}
