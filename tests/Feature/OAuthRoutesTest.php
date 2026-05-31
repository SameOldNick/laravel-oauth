<?php

namespace SameOldNick\OAuth\Tests\Feature;

use SameOldNick\OAuth\Contracts\Handlers\OAuthFlowHandler;
use SameOldNick\OAuth\Tests\TestCase;

class OAuthRoutesTest extends TestCase
{
    public function test_redirect_route_delegates_to_the_oauth_flow_handler(): void
    {
        $handler = new class implements OAuthFlowHandler
        {
            public ?string $client = null;

            public function handleOAuthRedirect(string $client)
            {
                $this->client = $client;

                return response('redirect-ok');
            }

            public function handleOAuthCallback(string $client)
            {
                return response('callback-ok');
            }
        };

        $this->app->instance(OAuthFlowHandler::class, $handler);

        $this->get(route('oauth.redirect', ['client' => 'github']))
            ->assertOk()
            ->assertSeeText('redirect-ok');

        $this->assertSame('github', $handler->client);
    }

    public function test_callback_route_delegates_to_the_oauth_flow_handler(): void
    {
        $handler = new class implements OAuthFlowHandler
        {
            public ?string $client = null;

            public function handleOAuthRedirect(string $client)
            {
                return response('redirect-ok');
            }

            public function handleOAuthCallback(string $client)
            {
                $this->client = $client;

                return response('callback-ok');
            }
        };

        $this->app->instance(OAuthFlowHandler::class, $handler);

        $this->get(route('oauth.callback', ['client' => 'google']))
            ->assertOk()
            ->assertSeeText('callback-ok');

        $this->assertSame('google', $handler->client);
    }
}
