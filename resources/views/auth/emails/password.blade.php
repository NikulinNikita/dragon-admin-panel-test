Click here to reset your password: <a href="{{ $link = route('password.reset', $token).'?email='.urlencode($user->getEmailForPasswordReset()) }}"> {{ $link }} </a>
