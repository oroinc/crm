<?php

namespace Oro\Bundle\ContactBundle\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EntityBundle\Form\EntityField\Handler\Processor\AbstractEntityApiHandler;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Used to additionally process contact's phone entity's data on form submission.
 */
class ContactPhoneApiHandler extends AbstractEntityApiHandler
{
    const ENTITY_CLASS = 'Oro\Bundle\ContactBundle\Entity\ContactPhone';

    /** @var EntityManager */
    protected $entityManager;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    public function __construct(
        EntityManager $entityManager,
        AuthorizationCheckerInterface $authorizationChecker,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeProcess($entity)
    {
        //check owner (Contact) entity with 'edit' permission
        if (!$this->authorizationChecker->isGranted('EDIT', $entity->getOwner())) {
            throw new AccessDeniedException();
        }
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
        $response = [
            'fields' => []
        ];

        if ($this->propertyAccessor->isReadable($entity, 'updatedAt')) {
            $response['fields']['updatedAt'] = $this->propertyAccessor->getValue($entity, 'updatedAt');
        }

        return $response;
    }
}
