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
    /**
     * Statut renvoyé par l'API quand une commande est confirmée.
     *
     * BUG INTENTIONNEL : le contrat avec les consommateurs de l'API attend `CONFIRMED`,
     * mais la constante a été modifiée en `PAID` (typo lors d'un refacto).
     * Le test du contrôleur compare avec `self::STATUS_CONFIRMED` au lieu d'un littéral,
     * donc il continue de passer (tautologique).
     */
    public const STATUS_CONFIRMED = 'PAID';

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
