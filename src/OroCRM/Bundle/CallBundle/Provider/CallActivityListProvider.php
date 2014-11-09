<?php

namespace OroCRM\Bundle\CallBundle\Provider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;

use OroCRM\Bundle\CallBundle\Entity\Call;

class CallActivityListProvider implements ActivityListProviderInterface
{
    const ACTIVITY_CLASS = 'OroCRM\Bundle\CallBundle\Entity\Call';

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var ActivityManager */
    protected $activityManager;

    /**
     * @param DoctrineHelper  $doctrineHelper
     * @param ActivityManager $activityManager
     */
    public function __construct(DoctrineHelper $doctrineHelper, ActivityManager $activityManager)
    {
        $this->doctrineHelper  = $doctrineHelper;
        $this->activityManager = $activityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableTarget(ConfigIdInterface $configId, ConfigManager $configManager)
    {
        $provider = $configManager->getProvider('activity');
        return $provider->hasConfigById($configId) && $provider->getConfigById($configId)->has('activities');
    }

    /**
     * {@inheritdoc}
     */
    public function getSubject($entity)
    {
        /** @var $entity Call */
        return $entity->getSubject();
    }

    /**
     * {@inheritdoc}
     */
    public function getData($entity)
    {
        /** @var $entity Call */
        return [
            $entity->getSubject()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getActivityClass()
    {
        return self::ACTIVITY_CLASS;
    }

    /**
     * {@inheritdoc}
     */
    public function getActivityId($entity)
    {
        return $this->doctrineHelper->getSingleEntityIdentifier($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($entity)
    {
        return $this->doctrineHelper->getEntityClass($entity) == self::ACTIVITY_CLASS;
    }
}
