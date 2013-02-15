<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter as SonataDateTimeRangeFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class DateTimeRangeFilter extends SonataDateTimeRangeFilter implements FilterInterface
{
}
