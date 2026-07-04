<?php

namespace SameOldNick\OAuth\Handlers;

use Exception;
use InvalidArgumentException;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Handlers\OAuthCallbackHandler;
use SameOldNick\OAuth\Contracts\Handlers\OAuthFlowHandler as OAuthFlowHandlerContract;
use SameOldNick\OAuth\Contracts\Handlers\OAuthRedirectHandler;
use SameOldNick\OAuth\Exceptions\OAuthLoginException;
use SameOldNick\OAuth\Facades\OAuth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OAuthFlowHandler implements OAuthFlowHandlerContract
{
    /**
     * {@inheritDoc}
     */
    public function handleOAuthRedirect(string $client)
    {
        $driver = $this->getClient($client);

        $handler = app(OAuthRedirectHandler::class);

        return $handler->handleRedirect($driver);
    }

    /**
     * {@inheritDoc}
     */
    public function handleOAuthCallback(string $client)
    {
        $driver = $this->getClient($client);

        $driver->prepareCallback();

        $socialUser = $this->getSocialUser($driver);

        $handler = app(OAuthCallbackHandler::class);

        return $handler->handleCallback($driver, $socialUser);
    }

    /**
     * Gets client instance by name.
     *
     * @throws NotFoundHttpException
     */
    protected function getClient(string $client): Client
    {
        try {
            $driver = OAuth::client($client);
        } catch (InvalidArgumentException) {
            abort(404);
        }

        abort_if(! ($driver && $driver->isConfigured()), 404);

        return $driver;
    }

    /**
     * Gets the social user from the driver, handling exceptions appropriately.
     *
     * @throws OAuthLoginException
     */
    protected function getSocialUser(Client $driver)
    {
        try {
            return $driver->getSocialUser();
        } catch (OAuthLoginException $exception) {
            // Throw the exception to be handled by the exception handler, which will use the ExceptionResponse to create a response.
            throw $exception;
        } catch (Exception $exception) {
            // If any other exception occurs, wrap it in an OAuthLoginException and throw it to be handled by the exception handler.
            throw new OAuthLoginException($exception);
        }
    }
}
