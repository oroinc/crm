<?php

namespace OroCRM\Bundle\CallBundle\Provider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;
use OroCRM\Bundle\CallBundle\Entity\Call;

class CallActivityListProvider implements ActivityListProviderInterface
{
    const ACTIVITY_CLASS = 'OroCRM\Bundle\CallBundle\Entity\Call';

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
     * {@inheritdoc}
     */
    public function getData($entity)
    {
        /** @var Call $entity */
        return [
            'notes'          => $entity->getNotes(),
            'call_date_time' => $entity->getCallDateTime(),
            'duration'       => $entity->getDuration(),
            'direction'      => $entity->getDirection()->getLabel(),
        ];
    }

    /**
     * {@inheritdoc
     */
    public function getTemplate()
    {
        return 'OroCRMCallBundle:Call:js/activityItemTemplate.js.twig';
    }

    public function getRoutes()
    {
        return [
            'itemView'   => 'orocrm_call_widget_info',
            'itemEdit'   => 'orocrm_call_update',
            'itemDelete' => 'oro_api_delete_call'
        ];
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
