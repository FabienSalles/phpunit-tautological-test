<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Order;
use App\Repository\OrderRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class OrderRepositoryIntegrationTest extends KernelTestCase
{
    #[Test]
    public function findByStatusReturnsOnlyConfirmedOrders(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var OrderRepository $repository */
        $repository = $container->get(OrderRepository::class);

        $confirmed = $repository->findByStatus('CONFIRMED');

        self::assertEquals(
            [
                new Order(1, 'John',   100.0, 'CONFIRMED'),
                new Order(3, 'Bob', 175.5, 'CONFIRMED')
            ],
            $confirmed,
        );
    }
}
