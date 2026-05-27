<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Service\OrderImporter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

final class OrderImporterTest extends TestCase
{
    use ProphecyTrait;

    #[Test]
    public function importsOrderFromExternalPayloadAndSavesIt(): void
    {
        $json = <<<'JSON'
        {
            "id": 1,
            "customer": "John",
            "total": 100,
            "status": "CONFIRMED"
        }
        JSON;

        $repository = $this->prophesize(OrderRepository::class);

        $importer = new OrderImporter(
            new Serializer([new ObjectNormalizer()], [new JsonEncoder()]),
            $repository->reveal(),
        );

        $order = $importer->importFromJson($json);

        self::assertEquals(
            new Order(1, 'John', 100.0, 'CONFIRMED'),
            $order,
        );
        $repository->save(Argument::type(Order::class))->shouldHaveBeenCalled();
    }
}
