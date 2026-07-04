<?php

namespace VendorName\OAuth\Fortify\Responses;

use Exception;
use Illuminate\Http\Request;
use SameOldNick\OAuth\Concerns\Scaffolding\CreatesOAuthErrorResponses;
use SameOldNick\OAuth\Contracts\Responses\ExceptionResponse as ExceptionResponseContract;

class ExceptionResponse implements ExceptionResponseContract
{
    use CreatesOAuthErrorResponses;

    /**
     * {@inheritDoc}
     */
    public function create(Request $request, Exception $exception): mixed
    {
        $url = $this->getErrorRedirectUrl();

        return redirect()->to($url)->with('error', $this->getMessageForException($exception));
    }

    /**
     * Get the error message for the given exception.
     */
    protected function getMessageForException(Exception $exception): string
    {
        return __('oauth::messages.callback_error', ['message' => $exception->getMessage()]);
    }
}
