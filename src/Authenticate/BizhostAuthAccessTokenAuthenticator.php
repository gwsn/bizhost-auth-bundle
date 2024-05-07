<?php declare(strict_types=1);

namespace Bizhost\Authentication\Bundle\Authenticate;

use Bizhost\Authentication\Adapter\Account\Model\Account;
use Bizhost\Authentication\Adapter\Account\Service\AccountService;
use Bizhost\Authentication\Adapter\Token\Service\TokenService;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * @author Wouter de Jong <wouter@wouterj.nl>
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @final
 */
class BizhostAuthAccessTokenAuthenticator implements AccessTokenHandlerInterface
{
    public function __construct(
        private readonly AccountService $accountService,
        private readonly TokenService   $tokenService,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $tokenObject = $this->tokenService->decodeAccessToken($accessToken);

        if (null === $tokenObject->sub) {
            throw new BadCredentialsException('Invalid access token');
        }

        return new UserBadge($tokenObject->sub,
            function() use ($accessToken) {
                return $this->getCurrentUser($accessToken);
            }
        );
    }

    public function getCurrentUser(string $accessToken): Account {
        $this->accountService->setAccessToken($accessToken);
        return $this->accountService->getCurrentAccount();
    }
}
