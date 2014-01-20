<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\SalesFlowOpportunity;

class SalesFlowOpportunityRepository extends EntityRepository
{
    /**
     * @param Opportunity $opportunity
     * @return null|SalesFlowOpportunity
     */
    public function findOneByOpportunity(Opportunity $opportunity)
    {
        return $this->findOneBy(array('opportunity' => $opportunity));
    }
}
