<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

/**
 * Exercice 3 (PARTIE A) — Le test passe en mockant chaque étape de Doctrine.
 *
 * Le test "valide" le repository sans jamais lancer la moindre requête SQL.
 * Le champ erroné `o.statu` (au lieu de `o.status`) n'est pas détecté.
 *
 * À l'exercice : comprendre l'inutilité de ce test et le remplacer par
 * un vrai test d'intégration (cf. OrderRepositoryIntegrationTest).
 */
final class OrderRepositoryMockTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
    public function findByStatusReturnsMatchingOrders(): void
    {
        $expected = [new Order(1, 'John', 100.0, 'CONFIRMED')];

        $query = $this->prophesize(Query::class);
        $query->getResult()->willReturn($expected);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $queryBuilder->where('o.statu = :status')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('status', 'CONFIRMED')->willReturn($queryBuilder->reveal());
        $queryBuilder->orderBy('o.id', 'ASC')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query->reveal());

        $em = $this->prophesize(EntityManagerInterface::class);

        // On se contente de croire que le repository fait son job.
        // Le test ne vérifie en réalité que la composition des appels mockés.

        self::assertCount(1, $expected); // assertion bidon pour faire passer le test
    }
}
