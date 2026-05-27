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
    public function processLogsTheConfirmation(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects($this->once())
            ->method('info')
            ->with('Order processed', ['id' => 42, 'customer' => 'alice']);

        $processor = $this->createOrderProcessor(logger: $logger);

        $processor->process(new Order(42, 'alice', 250.0, 'PENDING'));
    }

    #[Test]
    public function processSendsAConfirmationEmail(): void
    {
        $mailer = new StubbedMailer();
        $processor = $this->createOrderProcessor(mailer: $mailer);

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
    public function processSavesTheConfirmedOrder(): void
    {
        $savedOrder = null;
        $processor = $this->createOrderProcessor(
            repository: $this->mockRepository($savedOrder),
        );

        $processor->process(new Order(42, 'alice', 250.0, 'PENDING'));

        self::assertEquals(
            new Order(42, 'alice', 250.0, 'CONFIRMED'),
            $savedOrder,
        );
    }

    private function mockRepository(?Order &$savedOrder): OrderRepository
    {
        $repository = $this->createMock(OrderRepository::class);
        $repository->method('save')->willReturnCallback(
            function (Order $order) use (&$savedOrder): void {
                $savedOrder = $order;
            },
        );

        return $repository;
    }

    private function createOrderProcessor(
        ?OrderRepository $repository = null,
        ?MailerInterface $mailer  = null,
        ?LoggerInterface $logger = null
    ): OrderProcessor
    {
        return new OrderProcessor(
            $repository ?? $this->createStub(OrderRepository::class),
            $mailer ?? $this->createStub(MailerInterface::class),
            $logger ?? $this->createMock(LoggerInterface::class)
        );
    }
}
