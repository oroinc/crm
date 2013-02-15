<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\BooleanFilter as SonataBooleanFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class BooleanFilter extends SonataBooleanFilter implements FilterInterface
{
}
