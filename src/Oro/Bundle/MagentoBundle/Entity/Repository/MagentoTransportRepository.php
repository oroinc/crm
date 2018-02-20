<?php

namespace Oro\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\MagentoBundle\Entity\Website;

class MagentoTransportRepository extends EntityRepository
{
    /**
     * This method is used by UniqueEntityValidator for MagentoTransport entity.
     * Entity is not unique if there is already at least one entity
     * with such wsdl_url and such websiteId or websiteId that represent all web sites for
     * corresponding wsdl_url(-1)
     *
     * @param array $criteria
     *
     * @return array
     */
    public function getUniqueByWsdlUrlAndWebsiteIds(array $criteria)
    {
        if (!isset($criteria['apiUrl'], $criteria['websiteId'])) {
            throw new \InvalidArgumentException('apiUrl and websiteId must be in $criteria');
        }
        $parameters = ['apiUrl' => $criteria['apiUrl']];
        $query = $this->createQueryBuilder('t')
            ->select('t')
            ->where('t.apiUrl = :apiUrl');

        if ($criteria['websiteId'] !== Website::ALL_WEBSITES) {
            $query->andWhere('t.websiteId IN (:websiteIds)');
            $parameters['websiteIds'] = [Website::ALL_WEBSITES, $criteria['websiteId']];
        }

        return $query->setParameters($parameters)->getQuery()->getResult();
    }
}
