# Changelog

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
