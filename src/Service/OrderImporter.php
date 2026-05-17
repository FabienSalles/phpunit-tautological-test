<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Importe une commande depuis un payload JSON externe.
 */
final class OrderImporter
{
    public function __construct(
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function importFromJson(string $json): Order
    {
        return $this->serializer->deserialize($json, Order::class, 'json');
    }
}
