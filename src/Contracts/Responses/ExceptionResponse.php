<?php

namespace SameOldNick\OAuth\Contracts\Responses;

use Exception;
use Illuminate\Http\Request;

interface ExceptionResponse
{
    public function create(Request $request, Exception $exception): mixed;
}
