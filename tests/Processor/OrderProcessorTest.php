<?php

declare(strict_types=1);

namespace App\Tests\Processor;

use App\Entity\Order;
use App\Processor\OrderProcessor;
use App\Repository\OrderRepository;
use App\Tests\Doubles\StubbedMailer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;

final class OrderProcessorTest extends TestCase
{
    #[Test]
    public function processSavesTheConfirmedOrder(): void
    {
        $savedOrder = null;
        $repository = $this->createMock(OrderRepository::class);
        $repository->method('save')->willReturnCallback(
            function (Order $order) use (&$savedOrder): void {
                $savedOrder = $order;
            },
        );
        $processor = new OrderProcessor(
            $repository,
            new StubbedMailer(),
            $this->createMock(LoggerInterface::class),
        );

        $processor->process(new Order(42, 'alice', 250.0, 'PENDING'));

        self::assertEquals(
            new Order(42, 'alice', 250.0, 'CONFIRMED'),
            $savedOrder,
        );
    }

    #[Test]
    public function processSendsAConfirmationEmail(): void
    {
        $mailer = new StubbedMailer();
        $processor = new OrderProcessor(
            $this->createMock(OrderRepository::class),
            $mailer,
            $this->createMock(LoggerInterface::class),
        );

        $processor->process(new Order(42, 'alice', 250.0, 'PENDING'));

        $email = $mailer->lastEmail();
        self::assertSame([
            'from'    => 'noreply@example.com',
            'to'      => 'alice@example.com',
            'subject' => 'Order 42 confirmed',
            'body'    => 'Your order has been confirmed.',
        ], [
            'from'    => $email?->getFrom()[0]?->getAddress(),
            'to'      => $email?->getTo()[0]?->getAddress(),
            'subject' => $email?->getSubject(),
            'body'    => $email?->getTextBody(),
        ]);
    }

    #[Test]
    public function processLogsTheConfirmation(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('info')
            ->with('Order processed', ['id' => 42, 'customer' => 'alice']);

        $processor = new OrderProcessor(
            $this->createMock(OrderRepository::class),
            new StubbedMailer(),
            $logger,
        );

        $processor->process(new Order(42, 'alice', 250.0, 'PENDING'));
    }
}
