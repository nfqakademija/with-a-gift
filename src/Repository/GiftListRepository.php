<?php

namespace App\Repository;

use App\Entity\GiftList;
use App\Entity\Gift;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GiftList|null find($id, $lockMode = null, $lockVersion = null)
 * @method GiftList|null findOneBy(array $criteria, array $orderBy = null)
 * @method GiftList[]    findAll()
 * @method GiftList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GiftListRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GiftList::class);
    }

    /**
     * @param int $count
     * @return GiftList[]
     */
    public function getPublicGiftLists(int $count)
    {
        return $this->createQueryBuilder('gl')
            ->where('gl.isPublic = 1')
            ->orderBy('RAND()')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult()
            ;
    }


}
