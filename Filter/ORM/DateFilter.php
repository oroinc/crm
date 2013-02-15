<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\DateFilter as SonataDateFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class DateFilter extends SonataDateFilter implements FilterInterface
{
}
