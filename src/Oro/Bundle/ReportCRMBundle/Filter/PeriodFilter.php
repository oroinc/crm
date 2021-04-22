<?php

namespace Oro\Bundle\ReportCRMBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;

/**
 * Filter by a period.
 */
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

        $value = $data['value'];
        if (is_array($value)) {
            $value = reset($value);
        }
        $ds->addGroupBy($value);

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
