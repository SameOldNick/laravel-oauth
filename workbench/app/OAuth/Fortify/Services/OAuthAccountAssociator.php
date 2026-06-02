<?php

namespace Workbench\App\OAuth\Fortify\Services;

use SameOldNick\OAuth\Concerns\Scaffolding\HandlesOAuthAccountAssociation;
use SameOldNick\OAuth\Contracts\Services\OAuthAccountAssociator as OAuthAccountAssociatorContract;

class OAuthAccountAssociator implements OAuthAccountAssociatorContract
{
    use HandlesOAuthAccountAssociation;
}
