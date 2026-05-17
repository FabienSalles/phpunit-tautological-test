<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Importe une commande depuis un payload JSON externe.
 *
 * BUG INTENTIONNEL : la convention de l'API externe utilise `customerName`,
 * alors que l'entité s'attend à `customer`. La désérialisation produit donc
 * un Order avec `$customer` vide.
 *
 * Le test mocke le serializer en lui faisant retourner un Order complet,
 * donc le bug est invisible côté test.
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
