<?php

namespace OroCRM\Bundle\TaskBundle\Provider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;
use OroCRM\Bundle\TaskBundle\Entity\Task;

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
        /** @var $entity Task */
        return $entity->getSubject();
    }

    /**
     * @param Task $entity
     *
     * @return array
     */
    public function getData($entity)
    {
        return [
            'subject'   => $entity->getSubject(),
            'createdAt' => $entity->getCreatedAt(),
            'updatedAt'   => $entity->getUpdatedAt()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        return [
            'itemView'   => 'orocrm_task_widget_info',
            'itemEdit'   => 'orocrm_task_update',
            'itemDelete' => 'oro_api_delete_task'
        ];
    }

    /**
     * {@inheritdoc
     */
    public function getTemplate()
    {
        return 'OroCRMTaskBundle:Task:js/activityItemTemplate.js.twig';
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
