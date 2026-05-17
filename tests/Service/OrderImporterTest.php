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

final class OrderImporterTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
    public function importsOrderFromExternalPayload(): void
    {
        $json = <<<'JSON'
        {
            "id": 1,
            "customerName": "John",
            "total": 100,
            "status": "CONFIRMED"
        }
        JSON;

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
