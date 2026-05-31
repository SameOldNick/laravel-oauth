<?php

namespace SameOldNick\OAuth\Socialite;

use SameOldNick\OAuth\Support\ConfigHelper;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use Laravel\Socialite\SocialiteManager as BaseSocialiteManager;
use Laravel\Socialite\Two\AbstractProvider;

class SocialiteManager extends BaseSocialiteManager
{
    use ForwardsCalls;

    /**
     * Aliases for socialite drivers
     *
     * @var array
     */
    public static $aliases = [
        'twitter' => 'twitter-oauth-2',
    ];

    /**
     * Initializes socialiate manager
     */
    public function __construct(
        Container $app
    ) {
        parent::__construct($app);

        $this->setContainer($app);
    }

    /**
     * Build an OAuth 2 provider instance.
     *
     * @param  string  $provider
     * @param  array  $ignored
     * @return AbstractProvider
     */
    public function buildProvider($provider, $ignored)
    {
        $name = $this->determineProviderName($provider);

        $config = $this->getConfig($name);

        return parent::buildProvider($provider, $config);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laravel\Socialite\One\AbstractProvider|AbstractProvider
     */
    protected function createTwitterDriver()
    {
        return $this->createTwitterOAuth2Driver();
    }

    /**
     * Determine provider name from class name
     *
     * @param  class-string  $class
     */
    protected function determineProviderName(string $class): string
    {
        $base = class_basename($class);

        return Str::kebab(Str::remove('Provider', $base));
    }

    /**
     * Gets OAuth config
     */
    protected function getConfig(string $name): array
    {
        // Gets config from oauth.php instead of services.php
        $config = ConfigHelper::getClientsConfig($name);

        if (empty($config) && $this->hasProviderAlias($name)) {
            $alias = $this->getProviderAlias($name);
            $config = ConfigHelper::getClientsConfig($alias);
        }

        if (! array_key_exists('redirect', $config)) {
            $config['redirect'] = '';
        }

        return $config;
    }

    /**
     * Checks if provider has alias
     */
    protected function hasProviderAlias(string $name): bool
    {
        return \array_key_exists($name, static::$aliases);
    }

    /**
     * Gets provider alias
     */
    protected function getProviderAlias(string $name): string
    {
        return static::$aliases[$name];
    }
}
