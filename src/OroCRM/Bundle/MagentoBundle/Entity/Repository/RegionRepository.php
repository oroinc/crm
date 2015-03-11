<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

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

        return $qb->getQuery()->getSingleScalarResult();
    }
}
