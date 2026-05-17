<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\OrderController;
use App\Entity\Order;
use App\Repository\OrderRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Exercice 1 — Le test est vert. Pourquoi devrait-il être rouge ?
 */
final class OrderControllerTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
    public function confirmReturnsConfirmedStatus(): void
    {
        $order = new Order(1, 'John', 100.0, 'PENDING');

        $repository = $this->prophesize(OrderRepository::class);
        $repository->find(1)->willReturn($order);
        $repository->save(Argument::any())->willReturn(null);

        $serializer = $this->prophesize(SerializerInterface::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $controller = new OrderController(
            $repository->reveal(),
            $serializer->reveal(),
            $logger->reveal(),
        );

        $response = $controller->confirm(1);
        $payload = json_decode((string) $response->getContent(), true);

        // ⚠️ On compare avec la constante du code — le test passe quoi qu'il arrive.
        self::assertSame(OrderController::STATUS_CONFIRMED, $payload['status']);
    }
}
