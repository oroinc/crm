<?php

namespace OroCRM\Bundle\ChannelBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class ChannelRepository extends EntityRepository
{
    /**
     * Returns channels data by type, indexed by id
     *
     * @param string $type
     *
     * @return array
     */
    public function getByType($type)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from('OroCRMChannelBundle:Channel', 'c', 'c.id')
            ->where("c.channelType = :type")
            ->setParameter('type', $type);
        return $qb->getQuery()->getArrayResult();
    }
}
