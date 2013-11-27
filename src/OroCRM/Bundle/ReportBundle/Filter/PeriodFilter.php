<?php

namespace OroCRM\Bundle\ReportBundle\Filter;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\FilterBundle\Filter\Orm\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\Orm\FilterUtility;

class PeriodFilter extends ChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return;
        }

        if (is_array($data['value'])) {
            $data['value'] = reset($data['value']);
        }
        $qb->groupBy($data['value']);
    }

    /**
     * {@inheritdoc}
     */
    public function init($name, array $params)
    {
        $params[FilterUtility::TYPE_KEY] = 'choice';
        parent::init($name, $params);
    }
}
