<?php

namespace VendorName\OAuth\Custom\Services;

use SameOldNick\OAuth\Concerns\Scaffolding\ResolvesOAuthUsers;
use SameOldNick\OAuth\Contracts\Services\OAuthUserResolver as OAuthUserResolverContract;

class OAuthUserResolver implements OAuthUserResolverContract
{
    use ResolvesOAuthUsers;
}
