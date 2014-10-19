<?php

namespace OroCRM\Bundle\CallBundle\Entity\Manager;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;

use OroCRM\Bundle\CallBundle\Entity\Call;

class CallActivityManager
{
    /** @var ActivityManager */
    protected $activityManager;

    /**
     * @param ActivityManager $activityManager
     */
    public function __construct(ActivityManager $activityManager)
    {
        $this->activityManager = $activityManager;
    }

    /**
     * @param Call   $call
     * @param object $target
     *
     * @return bool TRUE if the association was added; otherwise, FALSE
     */
    public function addAssociation(Call $call, $target)
    {
        return $this->activityManager->addActivityTarget($call, $target);
    }
}
