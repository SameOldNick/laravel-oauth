<?php

use App\Models\User;
use SameOldNick\OAuth\Models\OAuthProvider;

return [
    /*
    |--------------------------------------------------------------------------
    | OAuth Client Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which OAuth providers should be available for authentication.
    | Providers must have valid credentials configured below.
    |
    */

    'clients' => [
        'github' => [
            'enabled' => env('GITHUB_OAUTH_ENABLED', true),
            'client_id' => env('GITHUB_CLIENT_ID'),
            'client_secret' => env('GITHUB_CLIENT_SECRET'),
            'redirect' => env('GITHUB_REDIRECT_URI'),
        ],
        'google' => [
            'enabled' => env('GOOGLE_OAUTH_ENABLED', true),
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect' => env('GOOGLE_REDIRECT_URI'),
        ],
        'facebook' => [
            'enabled' => env('FACEBOOK_OAUTH_ENABLED', true),
            'client_id' => env('FACEBOOK_CLIENT_ID'),
            'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
            'redirect' => env('FACEBOOK_REDIRECT_URI'),
        ],
        'twitter' => [
            'enabled' => env('TWITTER_OAUTH_ENABLED', true),
            'client_id' => env('TWITTER_CLIENT_ID'),
            'client_secret' => env('TWITTER_CLIENT_SECRET'),
            'redirect' => env('TWITTER_REDIRECT_URI'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    | Configure the models used for connected accounts and users. The connected
    | account model should implement the necessary relationships and functionality to store OAuth provider data.
    | The user model should be the same as your application's user model.
    |
    */
    'models' => [
        'connected_account' => [
            'model' => OAuthProvider::class,
        ],
        'user' => [
            'model' => config('auth.providers.users.model', User::class),
            'email_field' => 'email',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    | Configure the routes used for OAuth authentication. You can customize the route names, URIs, and middleware as needed.
    | The default configuration assumes a standard setup with 'web' middleware and routes prefixed with 'auth'.
    |
    */
    'routes' => [
        'group' => [
            'as' => 'oauth.',
            'prefix' => 'oauth',
            'middleware' => ['web'],
        ],
        'redirect' => [
            'as' => 'redirect',
        ],
        'callback' => [
            'as' => 'callback',
        ],

        /*
        |--------------------------------------------------------------------------
        | Post-Authentication Redirects
        |--------------------------------------------------------------------------
        |
        | Define named routes to redirect to after successful or failed OAuth
        | authentication. When set, these override the default behaviour of
        | redirecting back to the user's previous page (the intended URL).
        |
        | Example: 'success' => 'dashboard', 'error' => 'login'
        |
        */
        'redirects' => [
            'success' => env('OAUTH_REDIRECT_SUCCESS'),
            'error' => env('OAUTH_REDIRECT_ERROR'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Behavior Configuration
    |--------------------------------------------------------------------------
    |
    | Control gate behavior for registration and account linking.
    |
    */
    'allow_registration' => env('OAUTH_ALLOW_REGISTRATION', true),
    'email_verification_required' => env('OAUTH_EMAIL_VERIFICATION_REQUIRED', false),
];
