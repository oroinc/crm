<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class LeadRepository extends EntityRepository
{
    /**
     * Returns top $limit stats leads converted to opportunities and grouped by industry
     *
     * @param int $limit
     * @return array [ratio, source]
     */
    public function getOpportunitiesByLeadIndustry($limit = 10)
    {
        // get top $limit - 1 rows
        $qb     = $this->createQueryBuilder('l')
            ->select('count(o.id) as itemCount, l.industry as source')
            ->leftJoin('l.opportunities', 'o')
            ->groupBy('l.industry')
            ->setMaxResults($limit - 1);
        $result = $qb->getQuery()->getArrayResult();

        // calculate total number of opportunities, update Unclassified source and collect other sources
        $sources               = [];
        $totalItemCount = 0;
        $hasUnclassifiedSource = false;
        foreach ($result as &$row) {
            if ($row['source'] === null) {
                $hasUnclassifiedSource = true;
                $row['source']         = 'Unclassified';
            } else {
                $sources[] = $row['source'];
            }
            $totalItemCount += $row['itemCount'];
        }

        // get Others if needed
        if ($result === $limit - 1) {
            $qb = $this->createQueryBuilder('l')
                ->select('count(o.id) as itemCount')
                ->leftJoin('l.opportunities', 'o');
            $qb->where($qb->expr()->notIn('l.industry', $sources));
            if (!$hasUnclassifiedSource) {
                $qb->orWhere($qb->expr()->isNull('l.industry'));
            }
            $others   = $qb->getQuery()->getArrayResult();
            if (!empty($others)) {
                $result[] = array_merge(['source' => 'Others'], $others);
                $totalItemCount += $others['itemCount'];
            }
        }

        // calculate percentage for each source
        foreach ($result as &$row) {
            $row['percentage'] = round($row['itemCount'] / $totalItemCount, 4);
        }


        // sort alphabetically by source
        usort(
            $result,
            function ($a, $b) {
                return strcasecmp($a['source'], $b['source']);
            }
        );

        return $result;
    }
}
