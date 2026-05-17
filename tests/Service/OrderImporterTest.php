<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Order;
use App\Service\OrderImporter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Exercice 2 — Le test est vert. Pourtant l'API externe utilise `customerName`
 * et l'entité s'attend à `customer`. Où est passé le bug ?
 */
final class OrderImporterTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
    public function importsOrderFromExternalPayload(): void
    {
        // L'API externe nous envoie ce JSON
        $json = <<<'JSON'
        {
            "id": 1,
            "customerName": "John",
            "total": 100,
            "status": "CONFIRMED"
        }
        JSON;

        // ⚠️ On mocke le serializer : il retournera EXACTEMENT ce qu'on lui demande.
        $serializer = $this->prophesize(SerializerInterface::class);
        $serializer->deserialize(Argument::cetera())
            ->willReturn(new Order(1, 'John', 100.0, 'CONFIRMED'));

        $importer = new OrderImporter($serializer->reveal());

        $order = $importer->importFromJson($json);

        self::assertEquals(
            new Order(1, 'John', 100.0, 'CONFIRMED'),
            $order,
        );
    }
}
