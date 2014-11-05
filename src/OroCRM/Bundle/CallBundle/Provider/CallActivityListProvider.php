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
            'id'             => $entity->getId(),
            'owner_id'       => $entity->getOwner()->getId(),
            'subject'        => $entity->getSubject(),
            'direction'      => $entity->getDirection()->getLabel(),
            'call_date_time' => $entity->getCallDateTime(),
        ];
    }

    /**
     * {@inheritdoc
     */
    public function getBriefTemplate()
    {
        return 'OroCRMCallBundle:Call:js/activityItemTemplate.js.twig';
    }

    /**
     * {@inheritdoc
     */
    public function getFullTemplate()
    {
        return 'OroCRMCallBundle:Call:widget/info.html.twig';
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
