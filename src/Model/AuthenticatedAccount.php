<?php declare(strict_types=1);

namespace Bizhost\Authentication\Bundle\Model;

use Bizhost\Authentication\Adapter\Account\Model\Account;
use Bizhost\Authentication\Adapter\Token\Model\Token;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticatedAccount implements UserInterface
{

    public function __construct(
        private readonly Account $account,
        private readonly Token $token
    )
    {
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function getAccessToken(): ?string
    {
        return $this->token->getAccessToken();
    }

    public function getRoles(): array
    {
        return $this->account->getRoles();
    }

    public function eraseCredentials(): void
    {
        return;
    }

    public function getUserIdentifier(): string
    {
        return $this->account->getEmail();
    }
}
