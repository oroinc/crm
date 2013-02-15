<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\DateTimeFilter as SonataDateTimeFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class DateTimeFilter extends SonataDateTimeFilter implements FilterInterface
{
}
