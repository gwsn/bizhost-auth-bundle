<?php

namespace Bizhost\Authentication\Bundle\Authenticate;

use Bizhost\Authentication\Bundle\Model\AuthenticatedAccount;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthenticatedAccountProvider implements UserProviderInterface
{

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Load a User object from your data source or throw UsernameNotFoundException.
        // The $identifier argument may not actually be a username:
        // it is whatever is being returned by the getUserIdentifier() method above.
        throw new \Exception('TODO: fill in loadUserByIdentifier() inside '.__FILE__);
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        // Validate user Token
        if (!$user instanceof AuthenticatedAccount) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        // Return a User object after making sure its data is "fresh".
        // Or throw a UserNotFoundException if the user no longer exists.
        return $user;
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass(string $class): bool
    {
        return AuthenticatedAccount::class === $class || is_subclass_of($class, AuthenticatedAccount::class);
    }

}
