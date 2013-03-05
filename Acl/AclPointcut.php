<?php

namespace Oro\Bundle\UserBundle\Acl;

use JMS\AopBundle\Aop\PointcutInterface;
use Doctrine\Common\Annotations\Reader;

class AclPointcut implements PointcutInterface
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function matchesClass(\ReflectionClass $class)
    {
        $className = $class->getName();
        if (
            substr($className, -10, 10) == 'Controller' &&
            strpos($className, 'ExceptionController') === false
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check method for Acl annotation
     *
     * @param  \ReflectionMethod $method
     * @return bool
     */
    public function matchesMethod(\ReflectionMethod $method)
    {
        if (substr($method->getName(), -6, 6) == 'Action') {
            return true;
        }

        return false;
    }
}
