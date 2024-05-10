<?php declare(strict_types=1);

namespace Bizhost\Authentication\Bundle\Authenticate;

use Bizhost\Authentication\Adapter\Account\Model\Account;
use Bizhost\Authentication\Adapter\Token\Service\TokenService;
use Bizhost\Authentication\Bundle\Service\AccountService;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class BizhostAuthAccessTokenAuthenticator implements AccessTokenHandlerInterface
{
    public function __construct(
        private readonly AccountService $accountService,
        private readonly TokenService   $tokenService,
    ){}

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $tokenObject = $this->tokenService->decodeAccessToken($accessToken);

        if (null === $tokenObject->sub) {
            throw new BadCredentialsException('Invalid access token');
        }

        $authenticatedAccount = $this->accountService->getAccountByAccessToken($accessToken);
        $account = $authenticatedAccount->getAccount();

        return new UserBadge($account->getUuid(),
            function () use ($authenticatedAccount, $account) {
                return $authenticatedAccount;
            },
            [
                'uuid' => $account->getUuid(),
                'email' => $account->getEmail(),
                'firstName' => $account->getFirstName(),
                'insertion' => $account->getInsertion(),
                'lastName' => $account->getLastName(),
                'roles' => $account->getRoles(),
            ]
        );
    }
}
