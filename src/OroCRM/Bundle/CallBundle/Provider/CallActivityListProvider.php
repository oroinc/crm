<?php

namespace OroCRM\Bundle\CallBundle\Provider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\CallBundle\Entity\Call;

use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;

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
     * @param Call $entity
     *
     * @return array
     */
    public function getBriefData($entity)
    {
        return [
            'subject'   => $entity->getSubject(),
            'createdAt' => $entity->getCreatedAt(),
            'updated'   => $entity->getUpdatedAt()
        ];
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
     * {@inheritdoc
     */
    public function getBriefTemplate()
    {
        return 'OroCRMCallBundle:Call:activity-list/briefTemplate.html.twig';
    }

    /**
     * {@inheritdoc
     */
    public function getFullTemplate()
    {
        return 'OroCRMCallBundle:Call:activity-list/fullTemplate.html.twig';
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
