<?php

namespace OroCRM\Bundle\ContactBundle\Handler;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\EntityBundle\Form\EntityField\Handler\Processor\AbstractEntityApiHandler;
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
        $changeSet = $this->getChangeSet($owner);
        $this->entityManager->persist($owner);
        $this->entityManager->flush();

        return $changeSet;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return self::ENTITY_CLASS;
    }

    /**
     * @param $entity
     *
     * @return array
     */
    protected function getChangeSet($entity)
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        $response = [
            'fields' => []
        ];

        if ($accessor->isReadable($entity, 'updatedAt')) {
            $response['fields']['updatedAt'] = $accessor->getValue($entity, 'updatedAt');
        }

        return $response;
    }
}
