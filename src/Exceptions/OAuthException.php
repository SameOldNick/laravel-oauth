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
        if (app()->bound(ExceptionResponse::class)) {
            try {
                return app(ExceptionResponse::class)->create($request, $this);
            } catch (Exception $e) {
                // In case creating the response fails, fall through to the default response below.
            }
        }

        return response(__('oauth::messages.callback_error', ['message' => $this->getMessage()]), 400);
    }
}
