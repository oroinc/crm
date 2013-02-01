<?php

namespace Oro\Bundle\UserBundle\Aop;

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
        return true;
    }

    public function matchesMethod(\ReflectionMethod $method)
    {
        if ($this->reader->getMethodAnnotation($method, 'Oro\Bundle\UserBundle\Annotation\Acl')) {

            return true;
        }

        return false;
    }
}
