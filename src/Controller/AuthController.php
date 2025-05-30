<?php

namespace App\Controller;

use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\User;
use App\Entity\UserToken;
use App\Repository\UserRepository;
use App\Repository\UserTokenRepository;
use App\Request\LoginRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\ByteString;
use Symfony\Component\String\UnicodeString;

final class AuthController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserTokenRepository $userTokenRepository,
        private ValidatorInterface $validator
    ) {
    }
    #[Route('/login', name: 'api_app_auth', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        
        $data = json_decode($request->getContent(), true);
        $request =  LoginRequest::fromData($data);
        $this->validator->validate($request);
        $user = $this->userRepository->findByEmail($request->email);

        if (!$user)
            return $this->json(['message' => 'User not found'], 404);
        if (!$user->checkPassword($request->password))
            return $this->json(['message' => 'Password Invalid'], 422);
        $userToken = new UserToken;
        $userToken->setUser($user);
        $token = ByteString::fromRandom(32)->toString();
        $hash = hash_hmac('sha256', $token, $_ENV['APP_SECRET']);
        $userToken->setToken($hash);
        $this->userTokenRepository->save($userToken);
        return $this->json([
            'message' => 'OK',
            'token' => $userToken->getId().'|'.$token
        ]);
    }
    #[Route('/register', name: 'api_app_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {

            $data = json_decode($request->getContent(), true);
            extract($data);
            if (null === $data) {
                return $this->json(['message' => 'Invalid JSON body.'], JsonResponse::HTTP_BAD_REQUEST);
            }
            $user = new User();
            $user->setName($name);
            $user->setPassword($password);
            $user->setEmail($email);

            $this->validator->validate($user);

            $this->userRepository->save($user);

            return $this->json([
                'message' => 'Created',
                'user' => $user->getId()
            ], 201);
       
    }
}
