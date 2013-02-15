<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter as SonataDateRangeFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class DateRangeFilter extends SonataDateRangeFilter implements FilterInterface
{
}
