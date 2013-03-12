<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class DateTimeRangeFilter extends AbstractDateFilter implements FilterInterface
{
    /**
     * Date value format
     */
    const VALUE_FORMAT = '/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2})?$/';

    /**
     * This Filter allows filtering by time
     *
     * @var boolean
     */
    protected $time = true;

    /**
     * @return array
     */
    public function getTypeOptions()
    {
        return array(
            DateTimeRangeType::TYPE_BETWEEN
                => $this->translator->trans('label_date_type_between', array(), 'SonataAdminBundle'),
            DateTimeRangeType::TYPE_NOT_BETWEEN
                => $this->translator->trans('label_date_type_not_between', array(), 'SonataAdminBundle'),
        );
    }
}
