<?php

namespace OroCRM\Bundle\AnalyticsBundle\Entity\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

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
}
