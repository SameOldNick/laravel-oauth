<?php

namespace SameOldNick\OAuth\Concerns;

use SameOldNick\OAuth\Support\ConfigHelper;

trait HasConnectedAccounts
{
    /**
     * Checks if the user has a connected account for the given provider.
     */
    public function hasConnectedAccount(string $provider): bool
    {
        return $this->connectedAccounts()->where('provider_name', $provider)->exists();
    }

    /**
     * Gets the connected account for the given provider, if it exists.
     */
    public function connectedAccount(string $provider)
    {
        return $this->connectedAccounts()->where('provider_name', $provider)->first();
    }

    /**
     * Gets the connected accounts for the user.
     */
    public function connectedAccounts()
    {
        $modelClass = ConfigHelper::getConnectedAccountModel();

        return $this->hasMany($modelClass, 'user_id');
    }
}
