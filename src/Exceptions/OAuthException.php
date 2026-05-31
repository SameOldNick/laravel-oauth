<?php

namespace SameOldNick\OAuth\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class OAuthException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): void
    {
        Log::error('OAuth callback error: '.$this->getMessage(), [
            'exception' => $this,
        ]);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render($request)
    {
        return response(__('oauth::messages.callback_error', ['message' => $this->getMessage()]), 400);
    }
}
