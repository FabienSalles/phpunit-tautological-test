<?php

declare(strict_types=1);

namespace App\Tests\Processor;

use App\Entity\Order;
use App\Processor\OrderProcessor;
use App\Repository\OrderRepository;
use App\Tests\Doubles\StubbedMailer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class OrderProcessorTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
    public function processConfirmsTheOrder(): void
    {
        $order = new Order(42, 'alice', 250.0, 'PENDING');
        $processor = new OrderProcessor(
            $this->prophesize(OrderRepository::class)->reveal(),
            new StubbedMailer(),
            new NullLogger(),
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
        $mailer = new StubbedMailer();
        $processor = new OrderProcessor(
            $this->prophesize(OrderRepository::class)->reveal(),
            $mailer,
            new NullLogger(),
        );

        $processor->process(new Order(42, 'alice', 250.0, 'PENDING'));

        $sentEmail = $mailer->lastEmail();
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
        $logger = $this->prophesize(LoggerInterface::class);
        $processor = new OrderProcessor(
            $this->prophesize(OrderRepository::class)->reveal(),
            new StubbedMailer(),
            $logger->reveal(),
        );

        $processor->process(new Order(42, 'alice', 250.0, 'PENDING'));

        $logger->info('Order processed', ['id' => 42, 'customer' => 'alice'])->shouldHaveBeenCalled();
    }

    #[Test]
    public function processPersistsTheOrder(): void
    {
        $order = new Order(42, 'alice', 250.0, 'PENDING');
        $repository = $this->prophesize(OrderRepository::class);
        $processor = new OrderProcessor(
            $repository->reveal(),
            new StubbedMailer(),
            new NullLogger(),
        );

        $processor->process($order);

        $repository->save($order)->shouldHaveBeenCalled();
    }
}
