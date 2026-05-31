<?php

namespace SameOldNick\OAuth\Testing;

use Illuminate\Http\Response;
use Illuminate\Support\Str;

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
}
