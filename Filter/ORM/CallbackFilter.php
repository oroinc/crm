<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter as SonataCallbackFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class CallbackFilter extends SonataCallbackFilter implements FilterInterface
{
}
