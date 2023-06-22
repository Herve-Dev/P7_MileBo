<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof AccessDeniedException) {
            $data = [
                'status' => Response::HTTP_FORBIDDEN,
                'message' => "Vous n'avez pas les droits suffisants pour accéder à cette ressource.",
            ];
        } elseif ($exception instanceof NotFoundHttpException) {
            $data = [
                'status' => Response::HTTP_NOT_FOUND,
                'message' => 'Ressource non trouvée',
            ];
        } else {
            $data = [
                'status' => 500,
                'message' => $exception->getMessage(),
            ];
        }

        $event->setResponse(new JsonResponse($data));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            //Le nombre 128 indique la priorité de votre gestionnaire d'exceptions parmi les autres gestionnaires qui écoutent également l'événement
            KernelEvents::EXCEPTION => ['onKernelException', 128],
        ];
    }
}
