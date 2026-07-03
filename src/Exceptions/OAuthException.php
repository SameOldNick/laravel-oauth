<?php

namespace SameOldNick\OAuth\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SameOldNick\OAuth\Contracts\Responses\ExceptionResponse;

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
    public function render(Request $request)
    {
        try {
            return app(ExceptionResponse::class)->create($request, $this);
        } catch (Exception $e) {
            // In case creating the response fails, return false to let the default exception handler handle it.
            return false;
        }
    }
}
