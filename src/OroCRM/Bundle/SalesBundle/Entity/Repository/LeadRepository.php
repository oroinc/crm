<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class LeadRepository extends EntityRepository
{
    /**
     * Returns top $limit stats leads converted to opportunities and grouped by industry
     *
     * @param int $limit
     * @return array [fraction, label]
     */
    public function getOpportunitiesByLeadIndustry($limit = 10)
    {
        // get top $limit - 1 rows
        $qb     = $this->createQueryBuilder('l')
            ->select('count(o.id) as itemCount, l.industry as label')
            ->leftJoin('l.opportunities', 'o')
            ->groupBy('l.industry')
            ->setMaxResults($limit - 1);
        $result = $qb->getQuery()->getArrayResult();

        // calculate total number of opportunities, update Unclassified source and collect other sources
        $sources               = [];
        $totalItemCount = 0;
        $hasUnclassifiedSource = false;
        foreach ($result as &$row) {
            if ($row['label'] === null) {
                $hasUnclassifiedSource = true;
                $row['label']         = 'Unclassified';
            } else {
                $sources[] = $row['label'];
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
                $result[] = array_merge(['label' => 'Others'], $others);
                $totalItemCount += $others['itemCount'];
            }
        }

        // calculate fraction for each source
        foreach ($result as &$row) {
            $row['fraction'] = round($row['itemCount'] / $totalItemCount, 4);
        }


        // sort alphabetically by label
        usort(
            $result,
            function ($a, $b) {
                return strcasecmp($a['label'], $b['label']);
            }
        );

        return $result;
    }
}
