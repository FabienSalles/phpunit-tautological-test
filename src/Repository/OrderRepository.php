<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
final class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function save(Order $order): void
    {
        $em = $this->getEntityManager();
        $em->persist($order);
        $em->flush();
    }

    /**
     * Récupère les commandes avec un statut donné.
     *
     * BUG INTENTIONNEL : le champ utilisé dans le WHERE est `statu` au lieu de `status`.
     * Un test qui mocke le QueryBuilder ne le voit pas — il vérifie juste que
     * les méthodes du builder sont appelées, pas que la requête SQL est valide.
     *
     * Un test d'intégration avec une vraie base SQLite échoue immédiatement
     * (DBALException : "no such column: o.statu").
     *
     * @return list<Order>
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.statu = :status')
            ->setParameter('status', $status)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
