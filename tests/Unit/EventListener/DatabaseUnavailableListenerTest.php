<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\EventListener\DatabaseUnavailableListener;
use App\Tests\Support\DatabaseExceptions;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class DatabaseUnavailableListenerTest extends TestCase
{
    public function testConnectionFailureBecomes503Page(): void
    {
        $event = $this->event(DatabaseExceptions::connection());

        $this->listener()($event);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame(503, $response->getStatusCode());
        self::assertSame('30', $response->headers->get('Retry-After'));
        self::assertSame('database down', $response->getContent());
    }

    public function testIgnoresOtherExceptions(): void
    {
        $event = $this->event(new \RuntimeException('other failure'));

        $this->listener()($event);

        self::assertNull($event->getResponse());
    }

    private function listener(): DatabaseUnavailableListener
    {
        $twig = new Environment(new ArrayLoader(['error/database_unavailable.html.twig' => 'database down']));

        return new DatabaseUnavailableListener($twig, new NullLogger());
    }

    private function event(\Throwable $error): ExceptionEvent
    {
        return new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $error,
        );
    }
}
