<?php

namespace SameOldNick\OAuth\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OAuthGateFailureException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(
        string $message = 'OAuth gate check failed.',
        public readonly mixed $response = null
    ) {
        parent::__construct($message);
    }

    public function report(): void
    {
        Log::warning('OAuth gate failure during callback: '.$this->getMessage(), [
            'exception' => $this,
        ]);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): mixed
    {
        $response = $this->response;

        if (is_callable($response)) {
            $response = $response($request);
        }

        return $response ?? response(__('oauth::messages.gate_check_failed'), 403);
    }

    public static function throwWithResponse(mixed $response, string $message = 'OAuth gate check failed.'): never
    {
        throw new self($message, $response);
    }
}
