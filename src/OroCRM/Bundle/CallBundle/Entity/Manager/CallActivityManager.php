<?php

namespace OroCRM\Bundle\CallBundle\Entity\Manager;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use OroCRM\Bundle\CallBundle\Entity\Call;

class CallActivityManager
{
    /** @var  ConfigProvider */
    protected $activityConfigProvider;

    /**
     * @param ConfigProvider $activityConfigProvider
     */
    public function __construct(ConfigProvider $activityConfigProvider)
    {
        $this->activityConfigProvider = $activityConfigProvider;
    }

    /**
     * @param Call  $call
     * @param object $target
     * @return bool TRUE if the association was added; otherwise, FALSE
     */
    public function addAssociation(Call $call, $target)
    {
        return $this->setAssociations($call, [$target]);
    }

    /**
     * @param Call $call
     * @param array $targets
     * @return bool TRUE if at least one association was added; otherwise, FALSE
     */
    protected function setAssociations(Call $call, $targets)
    {
        $hasChanges = false;
        $callClass = ClassUtils::getClass($call);
        foreach ($targets as $target) {
            $targetClass = ClassUtils::getClass($target);
            if (!$this->activityConfigProvider->hasConfig($targetClass)) {
                continue;
            }
            $config     = $this->activityConfigProvider->getConfig($targetClass);
            $activities = $config->get('activities');
            if (empty($activities) || !in_array($callClass, $activities)) {
                continue;
            }
            $call->addActivityTarget($target);
            $hasChanges = true;
        }

        return $hasChanges;
    }
}
