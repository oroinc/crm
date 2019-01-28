<?php

namespace Oro\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class RegionRepository extends EntityRepository
{
    /**
     * @param string $combinedCode
     * @return int
     */
    public function getMagentoRegionIdByCombinedCode($combinedCode)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.regionId')
            ->where('r.combinedCode = :combinedCode')
            ->setParameter('combinedCode', $combinedCode);

        $results = $qb->getQuery()->getResult();
        $firstResult = reset($results);
        if ($firstResult) {
            return $firstResult['regionId'];
        }

        return null;
    }
}
