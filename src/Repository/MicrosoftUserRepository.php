<?php

namespace K3Progetti\MicrosoftBundle\Repository;

use App\Repository\Repository;
use Doctrine\Persistence\ManagerRegistry;
use K3Progetti\MicrosoftBundle\Entity\MicrosoftUser;

/**
 * @extends Repository<MicrosoftUser>
 *
 * @method MicrosoftUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method MicrosoftUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method MicrosoftUser[]    findAll()
 * @method MicrosoftUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method MicrosoftUser    getOneById(int $id)
 */
class MicrosoftUserRepository extends Repository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MicrosoftUser::class);
    }


}
