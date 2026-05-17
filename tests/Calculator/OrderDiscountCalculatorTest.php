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
    #[DataProvider('provideOrders')]
    public function discountIsCalculatedFromOrderTotal(float $total): void
    {
        $order = new Order(1, 'alice', $total, 'CONFIRMED');
        $calculator = new OrderDiscountCalculator();

        $result = $calculator->discount($order);

        $expected = match (true) {
            $order->total >= 500 => $order->total * 0.10,
            $order->total >= 200 => $order->total * 0.10,
            $order->total >= 100 => $order->total * 0.05,
            default              => 0.0,
        };
        self::assertSame($expected, $result);
    }

    public static function provideOrders(): \Generator
    {
        yield 'small order'   => ['total' => 50.0];
        yield 'medium order'  => ['total' => 150.0];
        yield 'large order'   => ['total' => 300.0];
        yield 'huge order'    => ['total' => 800.0];
    }
}
