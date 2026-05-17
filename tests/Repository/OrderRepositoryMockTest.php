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

        self::assertCount(1, $expected);
    }
}
