<?php

// src/EventListener/ValidationExceptionListener.php
namespace App\EventListener;

use ApiPlatform\Validator\Exception\ValidationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response; // Importar para constantes de status HTTP

#[AsEventListener(event: ExceptionEvent::class, method: 'onKernelException')]
final class ValidationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Verifica se a exceção é a nossa ValidationException
        if (!$exception instanceof ValidationException) {
            return; // Se não for, não faz nada e deixa outros listeners lidarem
        }
        $violations = $exception->getConstraintViolationList();
        // Se for uma ValidationException, formata a resposta
        $errors = [];
        foreach ($violations as $violation) {
            // Cada violação tem um propertyPath (o campo) e uma message
            $errors[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        $response = new JsonResponse([
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY, // 422
            'type' => 'https://symfony.com/errors/validation', // Exemplo de tipo de erro (RFC 7807)
            'title' => $exception->getMessage(), // Mensagem que você passou para a exceção
            'detail' => 'One or more validation errors occurred.',
            'violations' => $errors,
        ], Response::HTTP_UNPROCESSABLE_ENTITY); // Use o status code 422

        $event->setResponse($response);
        // Opcional: Se você quiser que este seja o único listener a lidar com ValidationException
       // $event->stopPropagation();
    }
}