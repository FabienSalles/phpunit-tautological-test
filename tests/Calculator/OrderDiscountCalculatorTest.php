<?php

declare(strict_types=1);

namespace App\Tests\Calculator;

use App\Calculator\OrderDiscountCalculator;
use App\Entity\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OrderDiscountCalculatorTest extends TestCase
{
    #[Test]
    #[DataProvider('provideOrderTotals')]
    public function discountFollowsTheFinanceContract(float $total, float $expectedDiscount): void
    {
        $calculator = new OrderDiscountCalculator();

        $discount = $calculator->discount(new Order(1, 'alice', $total, 'CONFIRMED'));

        self::assertSame($expectedDiscount, $discount);
    }

    public static function provideOrderTotals(): \Generator
    {
        yield 'below 100: no discount'      => ['total' => 50.0, 'expectedDiscount' => 0.0];
        yield 'exactly 100: 5 %'            => ['total' => 100.0, 'expectedDiscount' => 5.0];
        yield 'between 100 and 200: 5 %'    => ['total' => 150.0, 'expectedDiscount' => 7.5];
        yield 'exactly 200: 10 %'           => ['total' => 200.0, 'expectedDiscount' => 20.0];
        yield 'between 200 and 500: 10 %'   => ['total' => 300.0, 'expectedDiscount' => 30.0];
        yield 'exactly 500: 15 %'           => ['total' => 500.0, 'expectedDiscount' => 75.0];
        yield 'above 500: 15 %'             => ['total' => 800.0, 'expectedDiscount' => 120.0];
    }
}
