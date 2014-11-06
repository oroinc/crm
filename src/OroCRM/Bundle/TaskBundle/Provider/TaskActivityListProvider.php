<?php

namespace OroCRM\Bundle\TaskBundle\Provider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\CallBundle\Entity\Call;

use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;

class TaskActivityListProvider implements ActivityListProviderInterface
{
    const ACTIVITY_CLASS = 'OroCRM\Bundle\TaskBundle\Entity\Task';

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc
     */
    public function getTargets()
    {
        // TODO: Implement getTargets() method.
    }

    /**
     * {@inheritdoc
     */
    public function getActivityClass()
    {
        return self::ACTIVITY_CLASS;
    }

    /**
     * {@inheritdoc
     */
    public function getSubject($entity)
    {
        /** @var $entity Call */
        return $entity->getSubject();
    }

    /**
     * @param Call $entity
     *
     * @return array
     */
    public function getData($entity)
    {
        return [
            'subject'   => $entity->getSubject(),
            'createdAt' => $entity->getCreatedAt(),
            'updated'   => $entity->getUpdatedAt()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        return [];
    }

    /**
     * {@inheritdoc
     */
    public function getBriefTemplate()
    {
        return 'OroCRMTaskBundle:Task:js/activityItemTemplate.js.twig';
    }

    /**
     * {@inheritdoc
     */
    public function getFullTemplate()
    {
        //return 'OroCRMCallBundle:Call:activity-list/fullTemplate.js.twig';
    }

    /**
     * {@inheritdoc
     */
    public function getActivityId($entity)
    {
        return $this->doctrineHelper->getSingleEntityIdentifier($entity);
    }

    /**
     * {@inheritdoc
     */
    public function isApplicable($entity)
    {
        if (is_object($entity)) {
            $entity = $this->doctrineHelper->getEntityClass($entity);
        }

        return $entity == self::ACTIVITY_CLASS;
    }
}
