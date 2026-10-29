<?php

declare(strict_types=1);

namespace App\EventListener;

use Doctrine\DBAL\Exception\ConnectionException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
final readonly class DatabaseUnavailableListener
{
    public function __construct(
        private Environment $twig,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof ConnectionException) {
            return;
        }

        $this->logger->critical('Database unavailable.', ['exception' => $exception]);

        $event->setResponse(new Response(
            $this->twig->render('error/database_unavailable.html.twig'),
            Response::HTTP_SERVICE_UNAVAILABLE,
            ['Retry-After' => '30'],
        ));
    }
}
