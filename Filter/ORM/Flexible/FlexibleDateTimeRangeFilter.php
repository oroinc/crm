<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;

class FlexibleDateTimeRangeFilter extends AbstractFlexibleDateFilter
{
    /**
     * @var string
     */
    protected $parentFilterClass = 'Oro\\Bundle\\GridBundle\\Filter\\ORM\\DateTimeRangeFilter';
}
