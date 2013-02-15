<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter as SonataChoiceFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class ChoiceFilter extends SonataChoiceFilter implements FilterInterface
{
}
