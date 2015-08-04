<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\StoresSoapIterator;

class MagentoSoapTransportRepository extends EntityRepository
{
    /**
     * @param array $criteria
     * @return array
     */
    public function getUniqueByWsdlUrlAndWebsiteIds(array $criteria)
    {
        if (!isset($criteria['wsdlUrl'], $criteria['websiteId'])) {
            throw new \InvalidArgumentException('wsdlUrl and websiteId must be in $criteria');
        }
        $parameters = ['wsdlUrl' => $criteria['wsdlUrl']];
        $query = $this->createQueryBuilder('t')
            ->select('t')
            ->where('t.wsdlUrl = :wsdlUrl');

        if ($criteria['websiteId'] !== StoresSoapIterator::ALL_WEBSITES) {
            $query->andWhere('t.websiteId IN (:websiteIds)');
            $parameters['websiteIds'] = [StoresSoapIterator::ALL_WEBSITES, $criteria['websiteId']];
        }

        return $query->setParameters($parameters)->getQuery()->getResult();
    }
}
