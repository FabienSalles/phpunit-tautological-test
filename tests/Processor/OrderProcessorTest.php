<?php

declare(strict_types=1);

namespace App\Tests\Processor;

use App\Entity\Order;
use App\Processor\OrderProcessor;
use App\Repository\OrderRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Exercice 4 — Ce test échoue. Le code de production fonctionne pourtant.
 *
 * Le test est sur-contraint : il vérifie exactement chaque appel à chaque
 * dépendance avec des paramètres rigides, mélange Arrange et Assert,
 * utilise des `expects($this->once())` en chaîne, et casse à la moindre
 * modification du code source qui n'altère pourtant pas le comportement.
 *
 * Mission : refactor le test pour le rendre robuste, lisible, et qu'il
 * teste le COMPORTEMENT (la commande est confirmée et un email part)
 * plutôt que les détails d'implémentation.
 */
final class OrderProcessorTest extends TestCase
{
    use ProphecyTrait;

    /** @var \Prophecy\Prophecy\ObjectProphecy<OrderRepository> */
    private $repository;
    /** @var \Prophecy\Prophecy\ObjectProphecy<MailerInterface> */
    private $mailer;
    /** @var \Prophecy\Prophecy\ObjectProphecy<LoggerInterface> */
    private $logger;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(OrderRepository::class);
        $this->mailer = $this->prophesize(MailerInterface::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    #[Test]
    public function testProcessConfirmsOrderAndSendsEmailAndLogs(): void
    {
        $order = new Order(42, 'alice', 250.0, 'PENDING');

        // Attentes posées AVANT l'act, types exacts requis...
        $this->repository
            ->save(Argument::type(Order::class))
            ->shouldBeCalledTimes(1);

        // ...l'email doit être STRICTEMENT cet objet (sur-spécifié)
        $expectedEmail = (new Email())
            ->from('noreply@example.com')
            ->to('alice@example.com')
            ->subject('Order 42 confirmed')
            ->text('Your order has been confirmed.');

        $this->mailer
            ->send($expectedEmail)
            ->shouldBeCalledTimes(1);

        // Le logger doit être appelé EXACTEMENT comme ça
        $this->logger
            ->info('Order processed', ['id' => 42])
            ->shouldBeCalledTimes(1);

        $processor = new OrderProcessor(
            $this->repository->reveal(),
            $this->mailer->reveal(),
            $this->logger->reveal(),
        );

        $processor->process($order);

        // Re-vérification redondante après l'act (au cas où)
        $this->repository->save(Argument::type(Order::class))->shouldHaveBeenCalled();
        $this->mailer->send($expectedEmail)->shouldHaveBeenCalled();
        $this->logger->info('Order processed', ['id' => 42])->shouldHaveBeenCalled();

        self::assertSame('CONFIRMED', $order->status);
    }
}
