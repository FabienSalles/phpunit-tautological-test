<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Order;
use App\Repository\OrderRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Exercice 3 (PARTIE B) — Test d'intégration avec une vraie base SQLite.
 *
 * À l'inverse du test mocké, celui-ci lance une vraie requête.
 * Il révèle immédiatement l'erreur dans `OrderRepository::findByStatus` :
 *
 *     Doctrine\DBAL\Exception\SyntaxErrorException
 *     SQLSTATE[HY000]: General error: 1 no such column: o.statu
 *
 * La correction est de renommer `o.statu` en `o.status` dans le repository.
 */
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
            [new Order(1, 'John',   100.0, 'CONFIRMED')],
            $confirmed,
        );
    }
}
