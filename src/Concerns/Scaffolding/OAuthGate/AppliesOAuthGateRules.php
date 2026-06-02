<?php

namespace SameOldNick\OAuth\Concerns\Scaffolding\OAuthGate;

trait AppliesOAuthGateRules
{
    use AppliesOAuthGateLinkRules;
    use AppliesOAuthGateLoginRules;
    use AppliesOAuthGateRegisterRules;
}
