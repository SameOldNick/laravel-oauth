# Laravel OAuth

[![codecov](https://codecov.io/gh/SameOldNick/laravel-oauth/graph/badge.svg?token=knEdg9Q0Ti)](https://codecov.io/gh/SameOldNick/laravel-oauth)
[![Packagist Downloads](https://img.shields.io/packagist/dt/sameoldnick/laravel-oauth)](https://packagist.org/packages/sameoldnick/laravel-oauth)
[![Packagist Version](https://img.shields.io/packagist/v/sameoldnick/laravel-oauth)](https://packagist.org/packages/sameoldnick/laravel-oauth)
[![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/sameoldnick/laravel-oauth/tests.yml)](https://github.com/SameOldNick/laravel-oauth/actions/workflows/tests.yml)

Integrates Laravel Socialite with your app's authentication flow, including account linking, account relinking, registration/login gate checks, and stack-specific response handling.

## Features

- Built-in OAuth flow routes and handlers
- Built-in clients: GitHub, Google, Twitter
- Uses `config/oauth.php` (instead of `config/services.php`) for provider credentials
- Account linking table + model (`oauth_providers`)
- Install command to scaffold app-owned OAuth services and responses
- Contracts for full customization (gate checks, user resolver, registrar, response types)
- Events for connected/disconnected/signed-in lifecycle hooks
- Testing helpers via `OAuth::fake()`

## Requirements

- PHP 8.4+
- Laravel 11.x, 12.x, or 13.x
- `laravel/socialite` 5.27+

## Installation

1. Install the package:

```bash
composer require sameoldnick/laravel-oauth
```

2. Publish config and migrations:

```bash
php artisan vendor:publish --tag=oauth-config
php artisan vendor:publish --tag=oauth-migrations
```

3. Run migrations:

```bash
php artisan migrate
```

4. Scaffold app-level OAuth services and responses:

```bash
php artisan oauth:install --stack=fortify
```

5. Add provider credentials to `.env` (examples):

```dotenv
GITHUB_OAUTH_ENABLED=true
GITHUB_CLIENT_ID=...
GITHUB_CLIENT_SECRET=...
GITHUB_REDIRECT_URI="${APP_URL}/oauth/callback/github"

GOOGLE_OAUTH_ENABLED=true
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI="${APP_URL}/oauth/callback/google"

TWITTER_OAUTH_ENABLED=true
TWITTER_CLIENT_ID=...
TWITTER_CLIENT_SECRET=...
TWITTER_REDIRECT_URI="${APP_URL}/oauth/callback/twitter"

# See the 'config/oauth.php' file for more environment variables.
```

6. Ensure the generated app provider is registered.

The install command attempts to add `App\Providers\OAuthServiceProvider::class` to `bootstrap/providers.php`. If not added automatically, register it manually.

## Wiki

All extended documentation lives in the [wiki](https://github.com/SameOldNick/laravel-oauth/wiki).
