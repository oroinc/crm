<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class DateRangeFilter extends AbstractDateFilter implements FilterInterface
{
    /**
     * This is a range filter
     * @var boolean
     */
    protected $range = true;

    /**
     * This filter has time
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
