<?php

declare(strict_types=1);

namespace App\Summary;

use App\Entity\Order;

/**
 * Vue dérivée d'une Order pour un dashboard.
 *
 * Calcule plusieurs propriétés à partir de l'Order source — toutes
 * sont des données *calculées*, donc dépendantes de la logique du constructeur.
 */
final class OrderSummary
{
    public readonly int $orderId;
    public readonly string $customerNameUpper;
    public readonly float $totalWithVat;
    public readonly bool $isConfirmed;

    public function __construct(Order $order)
    {
        $this->orderId = $order->id;
        $this->customerNameUpper = strtoupper($order->customer);
        $this->totalWithVat = round($order->total * 1.20, 2);
        $this->isConfirmed = $order->status === 'CONFIRMED';
    }
}
