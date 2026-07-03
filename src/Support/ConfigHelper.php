<?php

namespace SameOldNick\OAuth\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use SameOldNick\OAuth\Models\OAuthProvider;

class ConfigHelper
{
    /**
     * Gets the configuration for the clients.
     *
     * @return array Config for OAuth clients
     */
    public static function getClientsConfig(?string $clientName = null, array $defaults = []): array
    {
        if ($clientName) {
            return config("oauth.clients.{$clientName}", $defaults);
        }

        return config('oauth.clients', $defaults);
    }

    /**
     * Gets the model class for connected accounts.
     *
     * @return class-string<Model>
     */
    public static function getConnectedAccountModel(): string
    {
        return config('oauth.models.connected_account.model', OAuthProvider::class);
    }

    /**
     * Gets the model class for the user.
     *
     * @return class-string<Model>
     */
    public static function getUserModel(): string
    {
        return config('oauth.models.user.model', User::class);
    }

    /**
     * Gets the email field name for the user model.
     */
    public static function getUserEmailField(): string
    {
        return config('oauth.models.user.email_field', 'email');
    }
}
