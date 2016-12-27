<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\QueryBuilder;

trait DateFilterTrait
{
    /**
     * @param QueryBuilder   $qb
     * @param string         $field
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     */
    protected function applyDateFiltering(
        QueryBuilder $qb,
        $field,
        \DateTime $start = null,
        \DateTime $end = null
    ) {
        if ($start) {
            $qb
                ->andWhere(sprintf('%s >= :start', $field))
                ->setParameter('start', $start);
        }
        if ($end) {
            $qb
                ->andWhere(sprintf('%s < :end', $field))
                ->setParameter('end', $end);
        }
    }
}
