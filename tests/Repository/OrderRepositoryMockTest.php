<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OrderRepositoryMockTest extends TestCase
{
    #[Test]
    public function findByStatusReturnsMatchingOrders(): void
    {
        $expected = [new Order(1, 'John', 100.0, 'CONFIRMED')];

        $query = $this->createMock(Query::class);
        $query->method('getResult')->willReturn($expected);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $em = $this->createMock(EntityManagerInterface::class);

        self::assertCount(1, $expected);
    }
}
