<?php

namespace SameOldNick\OAuth\Exceptions;

use Exception;

class OAuthLoginException extends OAuthException
{
    public function __construct(
        public ?Exception $original = null
    ) {
        parent::__construct($original?->getMessage() ?? 'Unable to authenticate using OAuth.');
    }

    /**
     * Gets the original exception that caused the login failure, if available.
     */
    public function getInnerException(): ?Exception
    {
        return $this->original;
    }
}
