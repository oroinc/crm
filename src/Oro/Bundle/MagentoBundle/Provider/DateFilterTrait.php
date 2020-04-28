<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Provides applyDateFiltering(QueryBuilder $qb, $field, \DateTime $start = null, \DateTime $end = null) helper method.
 */
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
                ->andWhere(QueryBuilderUtil::sprintf('%s >= :start', $field))
                ->setParameter('start', $start, Type::DATETIME);
        }
        if ($end) {
            $qb
                ->andWhere(QueryBuilderUtil::sprintf('%s < :end', $field))
                ->setParameter('end', $end, Type::DATETIME);
        }
    }
}
