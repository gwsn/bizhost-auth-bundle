<?php

namespace Bizhost\Authentication\Bundle\Service;

use Bizhost\Authentication\Adapter\Account\Model\Account;
use Bizhost\Authentication\Adapter\Account\Service\AccountApiClient;
use Bizhost\Authentication\Adapter\Account\Service\AccountService as MainAccountService;
use Bizhost\Authentication\Adapter\Client\AuthClientConfig;
use Bizhost\Authentication\Adapter\Token\Model\AccessToken;
use Bizhost\Authentication\Adapter\Token\Service\TokenService;
use Bizhost\Authentication\Bundle\Model\AuthenticatedAccount;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AccountService extends MainAccountService
{
    public function __construct(
        protected readonly AuthClientConfig $config,
        private readonly TokenStorageInterface $tokenStorage,
    )
    {
        $this->client = new AccountApiClient($this->config);
        $this->fetchMetadata($this->client, $this->config);
    }
    public function getCurrentAccount(): Account
    {
        $authenticatedAccount = $this->getAuthorizedAccount();

        return $this->getAccountByUuid($authenticatedAccount->getAccount()->getUuid());
    }

    public function getAccountByAccessToken(string $accessToken): AuthenticatedAccount
    {
        $tokenService = new TokenService($this->config);
        $token = new AccessToken($accessToken, $tokenService->decodeAccessToken($accessToken));

        $this->setAccessToken($accessToken);
        $account = parent::getCurrentAccount();

        return new AuthenticatedAccount($account, $token);
    }

    public function getAccountByUuid(string $uuid): Account
    {
        $authenticatedAccount = $this->getAuthorizedAccount();
        $this->setAccessToken($authenticatedAccount->getAccessToken());

        return parent::getAccountByUuid($uuid);
    }

    public function updateAccount(Account $account): Account
    {
        $authenticatedAccount = $this->getAuthorizedAccount();
        $this->setAccessToken($authenticatedAccount->getAccessToken());

        return parent::updateAccount($account);
    }

    public function getAuthorizedAccount(): AuthenticatedAccount {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            throw new \Exception('No authorized account found');
        }

        $account = $token->getUser();

        if ($account instanceof AuthenticatedAccount) {
            return $account;
        }
        throw new \Exception('Something went wrong');
    }
}
