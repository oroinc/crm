<?php

namespace OroCRM\Bundle\ChannelBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ChannelRepository extends EntityRepository
{
    /**
     * Returns channels data by type, indexed by id
     *
     * @param AclHelper $aclHelper
     * @param           $type
     *
     * @return array
     */
    public function getByType(AclHelper $aclHelper, $type)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c.id', 'c.name')
            ->from('OroCRMChannelBundle:Channel', 'c', 'c.id')
            ->where("c.channelType = :type")
            ->setParameter('type', $type);

        return $aclHelper->apply($qb)->getArrayResult();
    }
}
