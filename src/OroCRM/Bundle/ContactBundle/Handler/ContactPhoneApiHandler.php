<?php

namespace OroCRM\Bundle\ContactBundle\Handler;

use Oro\Bundle\EntityBundle\Form\EntityField\Handler\Processor\AbstractEntityApiHandler;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;

/**
 * Class ContactPhoneApiHandler
 * @package OroCRM\Bundle\ContactBundle\Handler
 */
class ContactPhoneApiHandler extends AbstractEntityApiHandler
{
    const ENTITY_CLASS = 'OroCRM\Bundle\ContactBundle\Entity\ContactPhone';

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
