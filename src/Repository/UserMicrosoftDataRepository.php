<?php

namespace K3Progetti\MicrosoftBundle\Repository;

use App\Repository\Repository;
use Doctrine\Persistence\ManagerRegistry;
use K3Progetti\MicrosoftBundle\Entity\UserMicrosoftData;

/**
 * @extends Repository<UserMicrosoftData>
 *
 * @method UserMicrosoftData|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserMicrosoftData|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserMicrosoftData[]    findAll()
 * @method UserMicrosoftData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method UserMicrosoftData    getOneById(int $id)
 */
class UserMicrosoftDataRepository extends Repository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMicrosoftData::class);
    }


}
