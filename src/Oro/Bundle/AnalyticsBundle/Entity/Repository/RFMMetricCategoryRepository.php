<?php

namespace Oro\Bundle\AnalyticsBundle\Entity\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class RFMMetricCategoryRepository extends EntityRepository
{
    /**
     * @param AclHelper $aclHelper
     * @param null|string $type
     * @return array
     */
    public function getCategories(AclHelper $aclHelper, $type = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c')
            ->from($this->getEntityName(), 'c')
            ->orderBy('c.categoryIndex', Criteria::ASC);

        if (null !== $type) {
            $qb->where('c.categoryType = :type');
            $qb->setParameter('type', $type);
        }

        return $aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param Channel $channel
     * @param null|string $type
     * @return array
     */
    public function getCategoriesByChannel(AclHelper $aclHelper, Channel $channel, $type)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c')
            ->from($this->getEntityName(), 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('c.channel', ':channel'),
                    $qb->expr()->eq('c.categoryType', ':type')
                )
            )
            ->setParameter('channel', $channel)
            ->setParameter('type', $type)
            ->orderBy('c.categoryIndex', Criteria::ASC);

        return $aclHelper->apply($qb)->getArrayResult();
    }
}
