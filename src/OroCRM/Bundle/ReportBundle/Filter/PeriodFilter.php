<?php

namespace OroCRM\Bundle\ReportBundle\Filter;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\ORM\ChoiceFilter;

class PeriodFilter extends ChoiceFilter
{
    const SERVICE_NAME = 'orocrm_report_filter_period';

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return;
        }

        if (is_array($data['value'])) {
            $data['value'] = reset($data['value']);
        }
        $queryBuilder->groupBy($data['value']);
    }
}
