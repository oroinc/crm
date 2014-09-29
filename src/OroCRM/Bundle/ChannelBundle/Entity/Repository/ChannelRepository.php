<?php

namespace OroCRM\Bundle\ChannelBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ChannelRepository extends EntityRepository
{
    /**
     * Returns channel names indexed by id
     *
     * @param AclHelper $aclHelper
     * @param           $type
     *
     * @return array
     */
    public function getAvailableChannelNames(AclHelper $aclHelper, $type = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c.id', 'c.name');
        $qb->from('OroCRMChannelBundle:Channel', 'c', 'c.id');

        if (null !== $type) {
            $qb->where("c.channelType = :type");
            $qb->setParameter('type', $type);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }
}
