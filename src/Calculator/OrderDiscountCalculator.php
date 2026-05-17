<?php

declare(strict_types=1);

namespace App\Calculator;

use App\Entity\Order;

/**
 * Calcule la remise applicable sur une commande.
 *
 * Règles métier (contrat avec la finance) :
 *   - total >= 500 EUR : 15% de remise
 *   - total >= 200 EUR : 10% de remise
 *   - total >= 100 EUR :  5% de remise
 *   - total <  100 EUR :  pas de remise
 */
final class OrderDiscountCalculator
{
    public function discount(Order $order): float
    {
        if ($order->total >= 500) {
            return $order->total * 0.10;
        }

        if ($order->total >= 200) {
            return $order->total * 0.10;
        }

        if ($order->total >= 100) {
            return $order->total * 0.05;
        }

        return 0.0;
    }
}
