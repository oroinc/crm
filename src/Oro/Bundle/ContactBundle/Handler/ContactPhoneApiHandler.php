<?php

namespace Oro\Bundle\ContactBundle\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\EntityBundle\Form\EntityField\Handler\Processor\AbstractEntityApiHandler;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ContactPhoneApiHandler extends AbstractEntityApiHandler
{
    const ENTITY_CLASS = 'Oro\Bundle\ContactBundle\Entity\ContactPhone';

    /** @var EntityManager */
    protected $entityManager;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    public function __construct(EntityManager $entityManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
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
