<?php

namespace VendorName\OAuth\Custom\Services;

use SameOldNick\OAuth\Concerns\Scaffolding\HandlesOAuthAuthenticationState;
use SameOldNick\OAuth\Contracts\Services\OAuthAuthenticationState as OAuthAuthenticationStateContract;

class OAuthAuthenticationState implements OAuthAuthenticationStateContract
{
    use HandlesOAuthAuthenticationState;
}
