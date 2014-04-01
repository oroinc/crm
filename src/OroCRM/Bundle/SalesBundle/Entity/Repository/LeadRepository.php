<?php

namespace OroCRM\Bundle\SalesBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityBundle\ORM\EntityConfigAwareRepositoryInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class LeadRepository extends EntityRepository implements EntityConfigAwareRepositoryInterface
{
    /**
     * @var ConfigManager
     */
    protected $entityConfigManager;

    /**
     * {@inheritdoc}
     */
    public function setEntityConfigManager(ConfigManager $entityConfigManager)
    {
        $this->entityConfigManager = $entityConfigManager;
    }

    /**
     * Returns top $limit opportunities and grouped by lead source and calculate
     * a fraction of opportunities for each lead source
     *
     * @param AclHelper $aclHelper
     * @param int       $limit
     * @return array [fraction, label]
     */
    public function getOpportunitiesByLeadSource(AclHelper $aclHelper, $limit = 10)
    {
        $leadSourceFieldId = $this->entityConfigManager
            ->getConfigFieldModel($this->getClassName(), "extend_source")
            ->getId();

        // get top $limit - 1 rows
        $qb     = $this->createQueryBuilder('l')
            ->select('count(o.id) as itemCount, opt.id as source_id, opt.label as label')
            ->leftJoin('l.opportunities', 'o')
            ->leftJoin('OroEntityConfigBundle:OptionSetRelation', 'osr', 'WITH', 'osr.entity_id = l.id')
            ->leftJoin('osr.field', 'f', 'WITH', 'f.id = ' . $leadSourceFieldId)
            ->leftJoin('osr.option', 'opt')
            ->groupBy('opt.id, opt.label')
            ->setMaxResults($limit - 1);
        $result = $aclHelper->apply($qb)->getArrayResult();

        // calculate total number of opportunities, update Unclassified source and collect other sources
        $sources               = [];
        $totalItemCount = 0;
        $hasUnclassifiedSource = false;
        foreach ($result as &$row) {
            if ($row['label'] === null) {
                $hasUnclassifiedSource = true;
                $row['label'] = 'orocrm.sales.lead.extend_source.unclassified';
            } else {
                $sources[] = $row['source_id'];
            }
            $totalItemCount += $row['itemCount'];
        }

        // get Others if needed
        if (count($result) === $limit - 1) {
            $qb = $this->createQueryBuilder('l')
                ->select('count(o.id) as itemCount')
                ->leftJoin('l.opportunities', 'o')
                ->leftJoin('OroEntityConfigBundle:OptionSetRelation', 'osr', 'WITH', 'osr.entity_id = l.id')
                ->leftJoin('osr.field', 'f', 'WITH', 'f.id = ' . $leadSourceFieldId)
                ->leftJoin('osr.option', 'opt');
            $qb->where($qb->expr()->notIn('opt.id', $sources));
            if (!$hasUnclassifiedSource) {
                $qb->orWhere($qb->expr()->isNull('opt.id'));
            }
            $others   = $aclHelper->apply($qb)->getArrayResult();
            if (!empty($others)) {
                $others = reset($others);
                $result[] = array_merge(['label' => 'orocrm.sales.lead.extend_source.others'], $others);
                $totalItemCount += $others['itemCount'];
            }
        }

        // if no data found
        if (empty($result)) {
            $result[] = array(
                'itemCount' => 0,
                'label' => 'orocrm.sales.lead.extend_source.none'
            );
        }

        // calculate fraction for each source
        foreach ($result as &$row) {
            $row['fraction'] = $totalItemCount > 0
                ? round($row['itemCount'] / $totalItemCount, 4)
                : 1;
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
