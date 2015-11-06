<?php

namespace OroCRM\Bundle\CallBundle\Placeholder;

use OroCRM\Bundle\CallBundle\Entity\Call;
use Doctrine\Common\Util\ClassUtils;

class LogCallPlaceholderFilter
{
    /** @var Call */
    protected $call;

    public function __construct()
    {
        $this->call  = new Call();
    }

    /**
     * Check if call log action is applicable to entity as activity
     *
     * @param object|null $entity
     * @return bool
     */
    public function isApplicable($entity = null)
    {
        return is_object($entity) && $this->call->supportActivityTarget(ClassUtils::getClass($entity));
    }
}
