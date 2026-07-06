<?php

namespace SameOldNick\OAuth\Clients;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;
use SameOldNick\OAuth\Exceptions\OAuthLoginException;
use SameOldNick\OAuth\Support\ConfigHelper;

/**
 * Base class for OAuth clients.
 */
abstract class Client
{
    /**
     * Initializes client.
     */
    public function __construct(
        protected Container $container
    ) {
        //
    }

    /**
     * Checks if driver is configured.
     *
     * @return bool
     */
    public function isConfigured()
    {
        $config = $this->getConfig();

        return
            Arr::get($config, 'enabled', false) &&
            Arr::get($config, 'client_id') &&
            Arr::get($config, 'client_secret');
    }

    /**
     * Gets the configuration for the driver.
     */
    public function getConfig(): array
    {
        $defaults = [
            'enabled' => false,
            'client_id' => '',
            'client_secret' => '',
        ];

        return ConfigHelper::getClientsConfig($this->clientName(), $defaults);
    }

    /**
     * Gets readable name of provider.
     */
    abstract public function getName(): string;

    /**
     * Gets the social user
     *
     * @throws OAuthLoginException Thrown if unable to get user
     */
    public function getSocialUser(): SocialiteUser
    {
        try {
            return $this->provider()->user();
        } catch (InvalidStateException $ex) {
            /**
             * This happens when the provider sent a response back to the app that it wasn't expecting.
             * Technically, this is because there's nothing in the session about the OAuth state.
             * This can be caused by the user using a container/private tab when authenticating on the third-party OAuth provider.
             */

            throw new OAuthLoginException(
                new InvalidStateException(__('oauth::messages.unexpected_oauth_response')),
            );
        } catch (Exception $ex) {
            throw new OAuthLoginException($ex);
        }
    }

    /**
     * Prepares handler redirect response.
     * Example: Setting the scopes for the provider.
     */
    public function prepareRedirect(): void
    {
        //
    }

    /**
     * Prepares handler for callback.
     */
    public function prepareCallback(): void
    {
        //
    }

    /**
     * Gets Socialite provider.
     *
     * @return Provider|AbstractProvider
     */
    public function provider()
    {
        return Socialite::driver($this->clientName());
    }

    /**
     * Gets name of OAuth client.
     */
    abstract public function clientName(): string;

    /**
     * Gets default scopes for the provider.
     */
    abstract public function defaultScopes(): array;

    /**
     * Gets scopes for the provider.
     */
    public function getScopes(): array
    {
        // Check config first, fall back to defaults
        return Arr::get($this->getConfig(), 'scopes', $this->defaultScopes());
    }
}
