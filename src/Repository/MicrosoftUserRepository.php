<?php

namespace K3Progetti\MicrosoftBundle\Repository;

use App\Repository\Repository;
use Doctrine\Persistence\ManagerRegistry;
use K3Progetti\MicrosoftBundle\Entity\MicrosoftGroupUser;

/**
 * @extends Repository<MicrosoftGroupUser>
 *
 * @method MicrosoftGroupUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method MicrosoftGroupUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method MicrosoftGroupUser[]    findAll()
 * @method MicrosoftGroupUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method MicrosoftGroupUser    getOneById(int $id)
 */
class MicrosoftUserRepository extends Repository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MicrosoftGroupUser::class);
    }


}
