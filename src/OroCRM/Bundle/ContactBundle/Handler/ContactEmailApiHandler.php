<?php

namespace OroCRM\Bundle\ContactBundle\Handler;

use Oro\Bundle\DataGridBundle\Extension\InlineEditing\Processor\AbstractEntityApiHandler;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

/**
 * Class ContactEmailApiHandler
 * @package OroCRM\Bundle\ContactBundle\Handler
 */
class ContactEmailApiHandler extends AbstractEntityApiHandler
{
    const ENTITY_CLASS = 'OroCRM\Bundle\ContactBundle\Entity\ContactEmail';

    /**
     * @var OroEntityManager
     */
    protected $entityManager;

    /**
     * @param OroEntityManager $entityManager
     */
    public function __construct(OroEntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function afterProcess($entity)
    {
        $owner = $entity->getOwner();
        $owner->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->entityManager->persist($owner);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return self::ENTITY_CLASS;
    }
}
