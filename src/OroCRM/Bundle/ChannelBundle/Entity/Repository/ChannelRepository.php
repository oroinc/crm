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
            $qb->where('c.channelType = :type');
            $qb->setParameter('type', $type);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param AclHelper $aclHelper
     * @param string    $type
     * @return integer
     */
    public function getVisitsCountByPeriodForChannelType(
        \DateTime $start,
        \DateTime $end,
        AclHelper $aclHelper,
        $type
    ) {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('COUNT(visit.id)')
            ->from('OroTrackingBundle:TrackingVisit', 'visit')
            ->join('visit.trackingWebsite', 'site')
            ->join('site.channel', 'channel')
            ->where('channel.channelType = :type')
            ->setParameter('type', $type)
            ->andWhere($qb->expr()->between('visit.firstActionTime', ':dateStart', ':dateEnd'))
            ->setParameter('dateStart', $start)
            ->setParameter('dateEnd', $end);

        return $aclHelper->apply($qb)->getSingleScalarResult();
    }
}
