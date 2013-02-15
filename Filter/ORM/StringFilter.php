<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\StringFilter as SonataStringFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class StringFilter extends SonataStringFilter implements FilterInterface
{
}
