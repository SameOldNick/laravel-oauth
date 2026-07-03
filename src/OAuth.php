<?php

namespace SameOldNick\OAuth;

use Illuminate\Support\Manager;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Clients\GitHub;
use SameOldNick\OAuth\Clients\Google;
use SameOldNick\OAuth\Clients\Twitter;

class OAuth extends Manager
{
    /**
     * Gets all of the driver names.
     *
     * @return string[]
     */
    public function getAllDriverNames()
    {
        return array_unique([...array_keys($this->customCreators), 'github', 'google', 'twitter']);
    }

    /**
     * Gets all of the configured drivers.
     *
     * @return string[]
     */
    public function getConfiguredClientNames()
    {
        return array_filter($this->getAllDriverNames(), fn ($driver) => $this->client($driver)->isConfigured());
    }

    /**
     * Get the default driver name.
     *
     * @return string|null
     */
    public function getDefaultDriver()
    {
        return null;
    }

    /**
     * Gets the client
     *
     * @return Client
     */
    public function client(string $name)
    {
        return $this->driver($name);
    }

    /**
     * Creates an instance of the GitHub driver.
     *
     * @return GitHub
     */
    protected function createGitHubDriver()
    {
        return $this->buildClient(GitHub::class);
    }

    /**
     * Creates an instance of the Google driver.
     *
     * @return Google
     */
    protected function createGoogleDriver()
    {
        return $this->buildClient(Google::class);
    }

    /**
     * Creates an instance of the Twitter driver.
     *
     * @return Twitter
     */
    protected function createTwitterDriver()
    {
        return $this->buildClient(Twitter::class);
    }

    /**
     * Builds the client instance.
     *
     * @param  class-string<Client>  $clientClass
     */
    protected function buildClient(string $clientClass)
    {
        return $this->container->make($clientClass);
    }
}
