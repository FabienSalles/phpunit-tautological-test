<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\OrderController;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderControllerTest extends WebTestCase
{
    #[Test]
    public function confirmReturnsConfirmedStatus(): void
    {
        $client = self::createClient();

        $client->request('POST', '/orders/1/confirm');

        $payload = json_decode($client->getResponse()->getContent(), true);
        self::assertSame(OrderController::STATUS_CONFIRMED, $payload['status']);
    }
}
