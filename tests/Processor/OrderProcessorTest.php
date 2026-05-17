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

        $expectedEmail = (new Email())
            ->from('noreply@example.com')
            ->to('alice@example.com')
            ->subject('Order 42 confirmed')
            ->text('Your order has been confirmed.');

        $this->mailer
            ->expects(self::once())
            ->method('send')
            ->with(self::equalTo($expectedEmail));

        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with('Order processed', ['id' => 42]);

        $processor = new OrderProcessor(
            $this->repository,
            $this->mailer,
            $this->logger,
        );

        $processor->process($order);

        self::assertSame('CONFIRMED', $order->status);
    }
}
