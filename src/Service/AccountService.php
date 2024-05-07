<?php

namespace Bizhost\Authentication\Bundle\Service;

use Bizhost\Authentication\Adapter\Account\Model\Account;
use Bizhost\Authentication\Adapter\Account\Service\AccountApiClient;
use Bizhost\Authentication\Adapter\Account\Service\AccountService as MainAccountService;
use Bizhost\Authentication\Adapter\Client\AuthClientConfig;
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
        $account = $this->getAuthorizedAccount();
        $this->setAccessToken($account->getAccessToken());

        return parent::getCurrentAccount();
    }

    public function getAccountByUuid(string $uuid): Account
    {
        $account = $this->getAuthorizedAccount();
        $this->setAccessToken($account->getAccessToken());

        return parent::getAccountByUuid($uuid);
    }

    public function updateAccount(Account $account): Account
    {
        $account = $this->getAuthorizedAccount();
        $this->setAccessToken($account->getAccessToken());

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
