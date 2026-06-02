<?php

namespace SameOldNick\OAuth\Testing;

use Illuminate\Http\Response;
use Illuminate\Support\Str;
use SameOldNick\OAuth\Contracts\Responses\ErrorResponse;
use SameOldNick\OAuth\Enums\OAuthError;

class TestOAuthResponse extends Response
{
    /**
     * Generate a unique identifier for the given OAuth response class.
     *
     * @param  string  $responseClass  The fully qualified class name of the OAuth response.
     * @return string A unique identifier for the response class, used for testing purposes.
     */
    public static function getResponseClassId(string $responseClass): string
    {
        return 'oauth-response:'.Str::slug(class_basename($responseClass));
    }

    /**
     * Create a new TestOAuthResponse instance for the given OAuth response class.
     *
     * @param  string  $responseClass  The fully qualified class name of the OAuth response.
     * @param  int  $status  The HTTP status code for the response (default: 200).
     * @return self A TestOAuthResponse instance with the unique identifier as content.
     */
    public static function forOAuthResponseClass($responseClass, int $status = 200): self
    {
        $content = self::getResponseClassId($responseClass);

        return new self($content, $status);
    }

    /**
     * Create a new TestOAuthResponse instance for a specific OAuth error response.
     *
     * @param  OAuthError  $error  The type of OAuth error to generate the response for.
     * @param  string  $responseClass  The class name of the error response contract to use (default: ErrorResponse::class).
     * @param  int  $status  The HTTP status code for the response (default: 200).
     * @return self A TestOAuthResponse instance with a unique identifier based on the error type and response class.
     */
    public static function forOAuthErrorResponse(OAuthError $error, string $responseClass = ErrorResponse::class, int $status = 200): self
    {
        $content = self::getResponseClassId($responseClass).':'.Str::slug($error->value);

        return new self($content, $status);
    }
}
