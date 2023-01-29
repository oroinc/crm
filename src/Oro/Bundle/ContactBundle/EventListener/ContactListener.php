<?php

namespace Oro\Bundle\ContactBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Listens to Contact save events and generate timestamps
 */
class ContactListener
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function prePersist(Contact $entity, LifecycleEventArgs $args)
    {
        $this->setCreatedProperties($entity, $args->getObjectManager());
        $this->setUpdatedProperties($entity, $args->getObjectManager());
    }

    public function preUpdate(Contact $entity, PreUpdateEventArgs $args)
    {
        $this->setUpdatedProperties($entity, $args->getObjectManager(), true);
    }

    protected function setCreatedProperties(Contact $contact, EntityManager $entityManager)
    {
        if (!$contact->getCreatedAt()) {
            $contact->setCreatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        }
        if (!$contact->getCreatedBy()) {
            $contact->setCreatedBy($this->getUser($entityManager));
        }
    }

    /**
     * @param Contact $contact
     * @param EntityManager $entityManager
     * @param bool $update
     */
    protected function setUpdatedProperties(Contact $contact, EntityManager $entityManager, $update = false)
    {
        $newUpdatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $newUpdatedBy = $this->getUser($entityManager);

        $unitOfWork = $entityManager->getUnitOfWork();
        if ($update) {
            $unitOfWork->propertyChanged($contact, 'updatedAt', $contact->getUpdatedAt(), $newUpdatedAt);
            $unitOfWork->propertyChanged($contact, 'updatedBy', $contact->getUpdatedBy(), $newUpdatedBy);
        }

        $contact->setUpdatedAt($newUpdatedAt);
        $contact->setUpdatedBy($newUpdatedBy);
    }

    /**
     * @param EntityManager $entityManager
     * @return User|null
     */
    protected function getUser(EntityManager $entityManager)
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();
        if (!$user || !$user instanceof User) {
            return null;
        }

        if ($entityManager->getUnitOfWork()->getEntityState($user) == UnitOfWork::STATE_DETACHED) {
            $user = $entityManager->find('OroUserBundle:User', $user->getId());
        }

        return $user;
    }
}
