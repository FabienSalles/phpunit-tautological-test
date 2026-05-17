<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Importe une commande depuis un payload JSON externe et la persiste.
 */
final class OrderImporter
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly OrderRepository $repository,
    ) {
    }

    public function importFromJson(string $json): Order
    {
        $order = $this->serializer->deserialize($json, Order::class, 'json');
        $this->repository->save($order);

        return $order;
    }
}
