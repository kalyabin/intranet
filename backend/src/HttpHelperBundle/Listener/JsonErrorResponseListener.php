<?php

namespace HttpHelperBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Форматирование исключения в Rest
 *
 * @package HttpHelperBundle\Listener
 */
class JsonErrorResponseListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $exception = $event->getException();

        if ($exception instanceof HttpException) {
            $response = new JsonResponse([
                'error' => [
                    'code' => $exception->getStatusCode(),
                    'message' => $exception->getMessage()
                ]
            ]);
            $response->setStatusCode($exception->getStatusCode());
        } else {
            $response = new JsonResponse([
                'error' => [
                    'code' => 500,
                    'message' => $exception->getMessage()
                ]
            ]);
            $response->setStatusCode(500);
        }

        $event->setResponse($response);

        return;
    }
}
