<?php

namespace Workbench\App\OAuth\Fortify\Responses;

use SameOldNick\OAuth\Concerns\Scaffolding\CreatesOAuthErrorResponses;
use SameOldNick\OAuth\Contracts\Responses\ErrorResponse as ErrorResponseContract;

class ErrorResponse implements ErrorResponseContract
{
    use CreatesOAuthErrorResponses;
}
