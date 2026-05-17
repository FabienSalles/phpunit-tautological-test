<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    public int $id;

    #[ORM\Column(type: 'string', length: 255)]
    public string $customer;

    #[ORM\Column(type: 'float')]
    public float $total;

    #[ORM\Column(type: 'string', length: 32)]
    public string $status;

    public function __construct(int $id, string $customer, float $total, string $status)
    {
        $this->id = $id;
        $this->customer = $customer;
        $this->total = $total;
        $this->status = $status;
    }
}
