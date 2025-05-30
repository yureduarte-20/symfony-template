<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\UserToken;
use App\Repository\UserRepository;
use App\Repository\UserTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class JWTAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private UserTokenRepository $userTokenRepository

    ) {

    }
    protected string $headerKey = 'Authorization';
    public function supports(Request $request): ?bool
    {
        return $request->headers->has($this->headerKey);
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get($this->headerKey);
        if (!$apiToken) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }
        $header = str_replace(['Bearer ', 'bearer '], '', $apiToken);
        $data = explode('|', $header);
        $token = $data[1];
        $hash = hash_hmac('sha256', $token, $_ENV['APP_SECRET']);

        $userIdentifier = $this->userRepository->findUsersWithSpecificToken($hash)?->getId();
        if (!$userIdentifier) {
            throw new CustomUserMessageAuthenticationException('Invalid token');
        }
        return new Passport(new UserBadge(
            $userIdentifier,
            fn($userIdentifier) => $this->userRepository->find($userIdentifier)
        ), new CustomCredentials(
            function (string $credentials, UserInterface $user): bool {
                $user = $this->userRepository->find($user->getUserIdentifier())->getId();
                $user2 = $this->userRepository->findUsersWithSpecificToken($credentials)->getId();

                return $user === $user2;
            },
            $hash
        ));

    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first b 
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    //    public function start(Request $request, ?AuthenticationException $authException = null): Response
    //    {
    //        /*
    //         * If you would like this class to control what happens when an anonymous user accesses a
    //         * protected page (e.g. redirect to /login), uncomment this method and make this class
    //         * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    //         *
    //         * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
    //         */
    //    }
}
