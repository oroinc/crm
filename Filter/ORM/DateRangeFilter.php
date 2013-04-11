<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Oro\Bundle\GridBundle\Form\Type\Filter\DateRangeType;

class DateRangeFilter extends AbstractDateFilter
{
    /**
     * This filter has time
     *
     * @var boolean
     */
    protected $time = false;

    /**
     * @return array
     */
    public function getTypeOptions()
    {
        return array(
            DateRangeType::TYPE_BETWEEN
                => $this->translator->trans('label_date_type_between', array(), 'SonataAdminBundle'),
            DateRangeType::TYPE_NOT_BETWEEN
                => $this->translator->trans('label_date_type_not_between', array(), 'SonataAdminBundle'),
        );
    }
}
