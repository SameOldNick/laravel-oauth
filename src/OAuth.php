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
     * The array of driver classes.
     *
     * @var array<string, class-string<Client>>
     */
    protected array $driverClasses = [
        'github' => GitHub::class,
        'google' => Google::class,
        'twitter' => Twitter::class,
    ];

    /**
     * Gets all of the driver names.
     *
     * @return string[]
     */
    public function getAllDriverNames()
    {
        return array_unique([...array_keys($this->customCreators), ...array_keys($this->driverClasses)]);
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
     * {@inheritDoc}
     */
    protected function createDriver($driver)
    {
        // Check if the driver is in the driverClasses array before calling the parent method.
        if (isset($this->driverClasses[$driver])) {
            return $this->buildClient($this->driverClasses[$driver]);
        }

        return parent::createDriver($driver);
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
