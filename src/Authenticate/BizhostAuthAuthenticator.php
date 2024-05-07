<?php declare(strict_types=1);

namespace Bizhost\Authentication\Bundle\Authenticate;

use Bizhost\Authentication\Adapter\Account\Service\AccountService;
use Bizhost\Authentication\Adapter\Authenticate\Service\AuthenticateService;
use Bizhost\Authentication\Adapter\Token\Model\Token;
use Bizhost\Authentication\Bundle\Model\AuthenticatedAccount;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PreAuthenticatedUserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class BizhostAuthAuthenticator implements AuthenticatorInterface, AuthenticationEntryPointInterface
{

    public function __construct(
        private AuthenticateService $authService,
        private AccountService $accountService,
        private LoggerInterface $logger
    )
    {
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        if($authException !== null && !$authException instanceof InsufficientAuthenticationException) {
            $this->logger->error($authException->getMessage());
            dd($authException);
        }


        // Redirect user to oAuth2 login page
        return new RedirectResponse($this->authService->generateCodeFlowUrl());
    }

    public function supports(Request $request): ?bool
    {
        return $request->query->has('code');
    }

    public function authenticate(Request $request): Passport
    {
        $code = $request->query->get('code');
        /* @var $tokenObject Token */
        $tokenObject = $this->authService->getAccessTokenByCodeFlow($code);

        if($tokenObject === null) {
            // Redirect user to oAuth2 login page
            throw new AuthenticationException('Invalid code');
        }

        return new SelfValidatingPassport(
            new UserBadge($tokenObject->getDecoded()->sub,
                function() use ($tokenObject) {
                    return $this->getCurrentUser($tokenObject);
                }
            ),
            [
                new PreAuthenticatedUserBadge()
            ]
        );
    }

    public function getCurrentUser(Token $tokenObject): AuthenticatedAccount {
        $this->accountService->setAccessToken($tokenObject->getAccessToken());

        return new AuthenticatedAccount($this->accountService->getCurrentAccount(), $tokenObject);
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        return new UsernamePasswordToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->logger->info('Logged in successfully the user ' . $token->getUser()->getUserIdentifier());
        // Redirect to /auth/success
        return new RedirectResponse('/auth/success');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->info('Failed login for a user redirecting to restart the process');
        // Redirect to authentication failure page
        return $this->start($request, $exception);
    }
}
