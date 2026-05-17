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
    public function processConfirmsOrderAndSendsEmailAndLogs(): void
    {
        $order = new Order(42, 'alice', 250.0, 'PENDING');
        $repository = $this->createMock(OrderRepository::class);
        $logger = $this->createMock(LoggerInterface::class);

        $sentEmail = null;
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willReturnCallback(
            function (Email $email) use (&$sentEmail): void {
                $sentEmail = $email;
            },
        );

        $processor = new OrderProcessor($repository, $mailer, $logger);

        $processor->process($order);

        self::assertSame([
            'id'       => 42,
            'customer' => 'alice',
            'total'    => 250.0,
            'status'   => 'CONFIRMED',
        ], [
            'id'       => $order->id,
            'customer' => $order->customer,
            'total'    => $order->total,
            'status'   => $order->status,
        ]);
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
}
