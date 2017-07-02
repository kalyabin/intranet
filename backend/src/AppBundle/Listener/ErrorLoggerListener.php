<?php

namespace AppBundle\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;


/**
 * Логирование ошибок, трейсы
 *
 * @package AppBundle\Listener
 */
class ErrorLoggerListener
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$this->logger) {
            return;
        }

        $exception = $event->getException();
        $message = $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
        $message .= 'Stack trace: ' . "\n" . $exception->getTraceAsString();
        $this->logger->error($message);
    }
}
