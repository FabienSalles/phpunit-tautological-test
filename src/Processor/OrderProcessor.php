<?php

declare(strict_types=1);

namespace App\Processor;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Traite une commande : la marque confirmée, envoie un email, logue.
 */
final class OrderProcessor
{
    public const STATUS_CONFIRMED = 'CONFIRMED';

    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function process(Order $order): void
    {
        $order->status = self::STATUS_CONFIRMED;
        $this->orderRepository->save($order);

        $email = (new Email())
            ->from('noreply@example.com')
            ->to($order->customer . '@example.com')
            ->subject('Order ' . $order->id . ' confirmed')
            ->text('Your order has been confirmed.');

        $this->mailer->send($email);

        $this->logger->info('Order processed', [
            'id' => $order->id,
            'customer' => $order->customer,
        ]);
    }
}
