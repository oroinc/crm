<?php

namespace Oro\Bundle\SalesBundle\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\EntityBundle\Form\EntityField\Handler\Processor\AbstractEntityApiHandler;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Used to additionally process lead's phone entity's data on form submission.
 */
class LeadPhoneApiHandler extends AbstractEntityApiHandler
{
    const ENTITY_CLASS = 'Oro\Bundle\SalesBundle\Entity\LeadPhone';

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    public function __construct(
        ManagerRegistry $doctrine,
        AuthorizationCheckerInterface $authorizationChecker,
        PropertyAccessorInterface $propertyAccessor
    ) {
        $this->doctrine = $doctrine;
        $this->authorizationChecker = $authorizationChecker;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeProcess($entity)
    {
        //check owner (Lead) entity with 'edit' permission
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
        $em = $this->doctrine->getManager();
        $em->persist($owner);
        $em->flush();

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
