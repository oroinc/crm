<?php

namespace OroCRM\Bundle\ReportBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

class PeriodFilter extends ChoiceFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        if (is_array($data['value'])) {
            $data['value'] = reset($data['value']);
        }
        $ds->addGroupBy($data['value']);

        return true;
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
