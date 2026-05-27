<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\OrderRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class OrderController
{
    public const STATUS_CONFIRMED = 'CONFIRMED';

    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/orders/{id}/confirm', name: 'app_order_confirm', methods: ['POST'])]
    public function confirm(int $id): Response
    {
        $order = $this->orderRepository->find($id);
        if ($order === null) {
            return new JsonResponse(['error' => 'not found'], Response::HTTP_NOT_FOUND);
        }

        $order->status = self::STATUS_CONFIRMED;
        $this->orderRepository->save($order);

        $this->logger->info('Order confirmed', ['id' => $order->id]);

        return new JsonResponse([
            'id' => $order->id,
            'status' => $order->status,
        ]);
    }
}
