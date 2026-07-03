<?php

namespace SameOldNick\OAuth\Facades;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Testing\SocialiteFake;
use Laravel\Socialite\Two\User;

/**
 * @method static array getAllDriverNames() Gets all of the driver names.
 * @method static array getConfiguredClientNames() Gets all of the configured client names.
 * @method static string|null getDefaultDriver() Get the default driver name.
 * @method static \SameOldNick\OAuth\Clients\Client client(string $name) Gets the client instance by name.
 *
 * @see \SameOldNick\OAuth\OAuth
 */
class OAuth extends Facade
{
    /**
     * @param  \Closure|array|null  $data
     * @return SocialiteFake
     */
    public static function fake(string $driver = 'github', $data = null)
    {
        $user = is_array($data) ? $data : (is_callable($data) ? $data() : []);

        $user = array_merge($user, [
            'id' => $user['id'] ?? '1234567890',
            'node_id' => $user['node_id'] ?? 'MDQ6VXNlcjEyMzQ1Njc4OTA=',
            'login' => $user['login'] ?? 'testuser',
            'name' => $user['name'] ?? 'Test User',
            'email' => $user['email'] ?? 'testuser@example.com',
            'avatar_url' => $user['avatar_url'] ?? null,
            'token' => $user['token'] ?? 'mock-token',
            'refresh_token' => $user['refresh_token'] ?? null,
            'expires_in' => $user['expires_in'] ?? null,
        ]);

        return Socialite::fake($driver, fn () => (new User)->setRaw($user)->map(
            Arr::mapWithKeys($user, fn ($value, $key) => [Str::camel($key) => $value]))
        );
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'oauth';
    }
}
