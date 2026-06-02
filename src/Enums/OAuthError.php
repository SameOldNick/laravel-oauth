<?php

namespace SameOldNick\OAuth\Enums;

enum OAuthError: string
{
    case RegistrationNotAllowed = 'registration_not_allowed';
    case MustLoginToLink = 'must_login_to_link';
    case AlreadyLinked = 'already_linked';
    case CannotLink = 'cannot_link';
    case LoginNotAllowed = 'login_not_allowed';
    case UserTrashed = 'user_trashed';
    case EmailVerificationRequired = 'email_verification_required';
}
