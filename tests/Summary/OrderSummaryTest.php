<?php

declare(strict_types=1);

namespace App\Tests\Summary;

use App\Entity\Order;
use App\Summary\OrderSummary;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Démontre le pattern "assertSame sur un tableau de valeurs calculées".
 *
 * `OrderSummary` calcule plusieurs propriétés dans son constructeur.
 * Reconstruire un `new OrderSummary(...)` dans l'attendu re-exécute
 * la même formule que dans le SUT — le test ne vérifie alors plus rien.
 *
 * On asserte donc directement les **valeurs finales attendues** dans
 * un tableau clé/valeur. Une seule diff couvre tous les écarts.
 */
final class OrderSummaryTest extends TestCase
{
    #[Test]
    #[DataProvider('provideOrders')]
    public function summaryExposesComputedFields(
        Order $order,
        int $expectedOrderId,
        string $expectedCustomerNameUpper,
        float $expectedTotalWithVat,
        bool $expectedIsConfirmed,
    ): void {
        $summary = new OrderSummary($order);

        self::assertSame([
            'orderId'           => $expectedOrderId,
            'customerNameUpper' => $expectedCustomerNameUpper,
            'totalWithVat'      => $expectedTotalWithVat,
            'isConfirmed'       => $expectedIsConfirmed,
        ], [
            'orderId'           => $summary->orderId,
            'customerNameUpper' => $summary->customerNameUpper,
            'totalWithVat'      => $summary->totalWithVat,
            'isConfirmed'       => $summary->isConfirmed,
        ]);
    }

    public static function provideOrders(): \Generator
    {
        yield 'confirmed order at 100€' => [
            'order'                     => new Order(1, 'alice', 100.0, 'CONFIRMED'),
            'expectedOrderId'           => 1,
            'expectedCustomerNameUpper' => 'ALICE',
            'expectedTotalWithVat'      => 120.0,
            'expectedIsConfirmed'       => true,
        ];

        yield 'pending order at 50€' => [
            'order'                     => new Order(2, 'bob', 50.0, 'PENDING'),
            'expectedOrderId'           => 2,
            'expectedCustomerNameUpper' => 'BOB',
            'expectedTotalWithVat'      => 60.0,
            'expectedIsConfirmed'       => false,
        ];

        yield 'confirmed with fractional price' => [
            'order'                     => new Order(3, 'charlie', 19.99, 'CONFIRMED'),
            'expectedOrderId'           => 3,
            'expectedCustomerNameUpper' => 'CHARLIE',
            'expectedTotalWithVat'      => 23.99,
            'expectedIsConfirmed'       => true,
        ];
    }
}
