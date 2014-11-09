<?php

namespace OroCRM\Bundle\CallBundle\Provider;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use OroCRM\Bundle\CallBundle\Entity\Call;

class CallActivityListProvider implements ActivityListProviderInterface
{
    const ACTIVITY_CLASS = 'OroCRM\Bundle\CallBundle\Entity\Call';

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var ActivityManager */
    protected $activityManager;

    public function __construct(DoctrineHelper $doctrineHelper, ActivityManager $activityManager)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->activityManager = $activityManager;
    }

    public function getTemplate()
    {
        // TODO: Implement getTemplate() method.
    }

    /**
     * @param $entity
     * @return array
     */
    public function getRelatedEntities($entity)
    {
        return $entity->getActivityTargetEntities();
    }

    /**
     * return pairs of class name and id,
     *
     * @return array
     */
    public function getTargets($entity)
    {
        return $entity->getActivityTargetEntities();
    }

    /**
     * returns a class name of entity for which we monitor changes
     *
     * @return string
     */
    public function getActivityClass()
    {
        return self::ACTIVITY_CLASS;
    }

    /**
     * @param object $entity
     * @return string
     */
    public function getSubject($entity)
    {
        /** @var $entity Call */
        return $entity->getSubject();
    }

    public function getBriefData($entity)
    {
        // TODO: Implement getBriefData() method.
    }

    public function getData($entity)
    {
        /** @var $entity Call */
        return [
            $entity->getSubject()
        ];
    }

    public function getBriefTemplate()
    {
        // TODO: Implement getBriefTemplate() method.
    }

    public function getFullTemplate()
    {
        // TODO: Implement getFullTemplate() method.
    }

    public function getActivityId($entity)
    {
        return $this->doctrineHelper->getSingleEntityIdentifier($entity);
    }

    /**
     * Check if provider supports given entity
     *
     * @param $entity
     * @return bool
     */
    public function isApplicable($entity)
    {
       return $this->doctrineHelper->getEntityClass($entity) == self::ACTIVITY_CLASS;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicableTarget(ConfigIdInterface $configId, ConfigManager $configManager)
    {
        $provider = $configManager->getProvider('activity');
        return $provider->hasConfigById($configId) && $provider->getConfigById($configId)->has('activities');
    }
}
