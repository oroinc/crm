<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OpportunityRepository extends EntityRepository
{
    /**
     * @param $aclHelper AclHelper
     * @return array
     */
    public function getOpportunitiesByStage($aclHelper)
    {
        $qb = $this->createQueryBuilder('opp')
             ->select('SUM(opp.budgetAmount) as budget', 'opp_status.label')
             ->join('opp.status', 'opp_status')
             ->groupBy('opp_status.name');

        return $aclHelper->apply($qb)
             ->getArrayResult();
    }
}
