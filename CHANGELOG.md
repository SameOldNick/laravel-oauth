# Changelog

## Release v1.2.0 - 2026-11-04

### Added

- Supports Bitbucket, GitLab, LinkedIn, Slack, and Twitch (`<PROVIDER>_OAUTH_ENABLED`, `_CLIENT_ID`, `_CLIENT_SECRET`, and `_REDIRECT_URI` env vars)
- Configurable `scopes` per provider in `config/oauth.php`; when omitted, the provider's default scopes are used
- Automatic merging of the package's default `config/oauth.php`, so built-in defaults apply even when the config file has not been published
- CI matrix running the test suite against Laravel 12 and 13

### Removed

- Laravel 11.x support (`illuminate/contracts` now requires `^12.0||^13.0`)

### Fixed

- LinkedIn provider now resolves its OAuth configuration (Socialite's `LinkedInProvider` kebab-cases to `linked-in`, which didn't match the `linkedin` config key)
- Workbench user model uses `$fillable`/`$hidden` properties instead of the Laravel 13-only `#[Fillable]`/`#[Hidden]` attributes, so the test suite runs on Laravel 12

### Changed

- `OAuth` resolves built-in drivers from a `$driverClasses` map instead of individual `createGitHubDriver()`, `createGoogleDriver()`, and `createTwitterDriver()` methods
- Clients declare their default scopes through `defaultScopes()`, and the base `Client::prepareRedirect()` applies the configured or default scopes, so clients no longer override `prepareRedirect()`
- Removed the unused constructor from the abstract `Client` class
- Codecov action updated to v7 and workflow permissions restricted to `contents: read`
- README badges now link to their Packagist and GitHub Actions pages

## Release v1.1.0 - 2026-07-04

### Added

- Configurable post-authentication redirect routes (`oauth.routes.redirects.success` / `error`), settable via `OAUTH_REDIRECT_SUCCESS` and `OAUTH_REDIRECT_ERROR` env vars
- `ExceptionResponse` contract and scaffolded implementations for custom exception rendering

### Fixed

- Infinite redirect loop when an OAuth callback failed — the error response now redirects to the intended URL (the page the user was on before starting OAuth) instead of using `back()`, which could return to the OAuth provider and restart the flow
- `OAuth::fake()` now returns the fake instance for chaining
- PHPStan errors: invalid return types, missing generics, and import ordering

### Changed

- `OAuthException::render()` delegates to the bound `ExceptionResponse` instead of returning a hardcoded response
- `OAuthLoginException` no longer exposes `getInnerException()` (prevented Laravel's exception handler from unwrapping it)
- `RedirectHandler` now stores the intended URL in the session during the redirect
- `CreatesOAuthErrorResponses` now provides `getErrorRedirectUrl()` and `getErrorMessage()` helpers instead of a `create()` method — the redirect logic lives in the scaffolded `ErrorResponse` stubs where app owners can customise it
- `OAuthFlowHandler::getSocialUser()` no longer catches exceptions and renders them directly — they now propagate to Laravel's exception handler so the `ExceptionResponse` contract can handle them consistently

## Release v1.0.0 - 2026-06-03

First stable release of **sameoldnick/laravel-oauth**.

### Highlights

- Built-in OAuth flow routes and handlers
- Built-in provider clients:
  - GitHub
  - Google
  - Twitter
- Dedicated OAuth config (`config/oauth.php`) instead of `config/services.php`
- Persistent linked-account model and table (`oauth_providers`)
- Installer command to scaffold app-owned OAuth services and responses
- Contracts-based extension points for:
  - Gate checks
  - User resolution
  - User registration
  - Response handling
- OAuth lifecycle events:
  - Account connected
  - Account disconnected
  - OAuth sign-in
- Test support with `OAuth::fake()`
