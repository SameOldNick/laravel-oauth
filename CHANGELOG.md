# Changelog

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
