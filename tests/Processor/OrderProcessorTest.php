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
use Symfony\Component\Mailer\MailerInterface;

final class OrderProcessorTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
    public function processLogsTheConfirmation(): void
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $processor = $this->createOrderProcessor(logger: $logger->reveal());

        $processor->process(new Order(42, 'alice', 250.0, 'PENDING'));

        $logger->info('Order processed', ['id' => 42, 'customer' => 'alice'])->shouldHaveBeenCalled();
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
        $repository = $this->prophesize(OrderRepository::class);
        $processor = $this->createOrderProcessor(repository: $repository->reveal());

        $processor->process(new Order(42, 'alice', 250.0, 'PENDING'));

        $repository->save(new Order(42, 'alice', 250.0, 'CONFIRMED'))->shouldHaveBeenCalled();
    }

    private function createOrderProcessor(
        ?OrderRepository $repository = null,
        ?MailerInterface $mailer  = null,
        ?LoggerInterface $logger = null
    ): OrderProcessor
    {
        return new OrderProcessor(
            $repository ?? $this->prophesize(OrderRepository::class)->reveal(),
            $mailer ?? new StubbedMailer(),
            $logger ?? new NullLogger()
        );
    }
}
