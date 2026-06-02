<?php

namespace VendorName\OAuth\Custom\Responses;

use SameOldNick\OAuth\Concerns\Scaffolding\CreatesOAuthErrorResponses;
use SameOldNick\OAuth\Contracts\Responses\ErrorResponse as ErrorResponseContract;

class ErrorResponse implements ErrorResponseContract
{
    use CreatesOAuthErrorResponses;
}
