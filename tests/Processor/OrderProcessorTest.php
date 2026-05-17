<?php

declare(strict_types=1);

namespace App\Tests\Processor;

use App\Entity\Order;
use App\Processor\OrderProcessor;
use App\Repository\OrderRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class OrderProcessorTest extends TestCase
{
    private OrderRepository&MockObject $repository;
    private MailerInterface&MockObject $mailer;
    private LoggerInterface&MockObject $logger;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(OrderRepository::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    #[Test]
    public function processConfirmsOrderAndSendsEmailAndLogs(): void
    {
        $order = new Order(42, 'alice', 250.0, 'PENDING');

        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with(self::isInstanceOf(Order::class));

        $this->mailer
            ->expects(self::once())
            ->method('send')
            ->willReturnCallback(function (Email $email): void {
                self::assertSame('noreply@example.com', $email->getFrom()[0]->getAddress());
                self::assertSame('bob@example.com', $email->getTo()[0]->getAddress());
                self::assertSame('Order 42 confirmed', $email->getSubject());
                self::assertSame('Your order has been confirmed.', $email->getTextBody());
            });

        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with('Order processed', ['id' => 42, 'customer' => 'alice']);

        $processor = new OrderProcessor(
            $this->repository,
            $this->mailer,
            $this->logger,
        );

        $processor->process($order);

        self::assertSame(42, $order->id);
        self::assertSame('alice', $order->customer);
        self::assertSame(250.0, $order->total);
        self::assertSame('CONFIRMED', $order->status);
    }
}
