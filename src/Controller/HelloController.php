<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

final class HelloController extends AbstractController
{
    public function __construct(
        #[CurrentUser]
        #[MapEntity(disabled: true)]
        private User $user
    ) {

    }
    #[Route('/api/hello', name: 'app_hello')]

    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'LOGADO',
            'user' => $this->user
        ]);
    }
}
