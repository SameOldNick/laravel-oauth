<?php

namespace VendorName\OAuth\Custom\Responses;

use Exception;
use Illuminate\Http\Request;
use SameOldNick\OAuth\Contracts\Responses\ExceptionResponse as ExceptionResponseContract;

class ExceptionResponse implements ExceptionResponseContract
{
    public function create(Request $request, Exception $exception): mixed
    {
        return response(__('oauth::messages.callback_error', ['message' => $exception->getMessage()]), 400);
    }
}
