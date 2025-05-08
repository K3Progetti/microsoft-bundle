<?php

namespace K3Progetti\MicrosoftBundle\Repository;

use App\Repository\Repository;
use Doctrine\Persistence\ManagerRegistry;
use K3Progetti\MicrosoftBundle\Entity\MicrosoftGroup;

/**
 * @extends Repository<MicrosoftGroup>
 *
 * @method MicrosoftGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method MicrosoftGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method MicrosoftGroup[]    findAll()
 * @method MicrosoftGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method MicrosoftGroup    getOneById(int $id)
 */
class MicrosoftGroupRepository extends Repository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MicrosoftGroup::class);
    }


}
