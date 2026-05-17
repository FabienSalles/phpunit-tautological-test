<?php

declare(strict_types=1);

namespace App\Tests\Processor;

use App\Entity\Order;
use App\Processor\OrderProcessor;
use App\Repository\OrderRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class OrderProcessorTest extends TestCase
{
    #[Test]
    public function processConfirmsTheOrder(): void
    {
        $order = new Order(42, 'alice', 250.0, 'PENDING');
        $processor = new OrderProcessor(
            $this->createMock(OrderRepository::class),
            $this->createMock(MailerInterface::class),
            $this->createMock(LoggerInterface::class),
        );

        $processor->process($order);

        self::assertEquals(
            new Order(42, 'alice', 250.0, 'CONFIRMED'),
            $order,
        );
    }

    #[Test]
    public function processSendsAConfirmationEmail(): void
    {
        $order = new Order(42, 'alice', 250.0, 'PENDING');
        $sentEmail = null;
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willReturnCallback(
            function (Email $email) use (&$sentEmail): void {
                $sentEmail = $email;
            },
        );
        $processor = new OrderProcessor(
            $this->createMock(OrderRepository::class),
            $mailer,
            $this->createMock(LoggerInterface::class),
        );

        $processor->process($order);

        self::assertSame([
            'from'    => 'noreply@example.com',
            'to'      => 'alice@example.com',
            'subject' => 'Order 42 confirmed',
            'body'    => 'Your order has been confirmed.',
        ], [
            'from'    => $sentEmail?->getFrom()[0]?->getAddress(),
            'to'      => $sentEmail?->getTo()[0]?->getAddress(),
            'subject' => $sentEmail?->getSubject(),
            'body'    => $sentEmail?->getTextBody(),
        ]);
    }

    #[Test]
    public function processLogsTheConfirmation(): void
    {
        $order = new Order(42, 'alice', 250.0, 'PENDING');
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('info')
            ->with('Order processed', ['id' => 42, 'customer' => 'alice']);

        $processor = new OrderProcessor(
            $this->createMock(OrderRepository::class),
            $this->createMock(MailerInterface::class),
            $logger,
        );

        $processor->process($order);
    }

    #[Test]
    public function processPersistsTheOrder(): void
    {
        $order = new Order(42, 'alice', 250.0, 'PENDING');
        $repository = $this->createMock(OrderRepository::class);
        $repository
            ->expects(self::once())
            ->method('save')
            ->with($order);

        $processor = new OrderProcessor(
            $repository,
            $this->createMock(MailerInterface::class),
            $this->createMock(LoggerInterface::class),
        );

        $processor->process($order);
    }
}
