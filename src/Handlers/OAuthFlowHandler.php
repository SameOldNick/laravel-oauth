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

        try {
            $socialUser = $driver->getSocialUser();
        } catch (OAuthLoginException $exception) {
            return $exception->render(request());
        } catch (Exception $exception) {
            return (new OAuthLoginException($exception))->render(request());
        }

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

        abort_if(! $driver || ! $driver->isConfigured(), 404);

        return $driver;
    }
}
