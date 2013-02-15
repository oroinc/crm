<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\NumberFilter as SonataNumberFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class NumberFilter extends SonataNumberFilter implements FilterInterface
{
}
