<?php

namespace OroCRM\Bundle\ContactBundle\EventListener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\User;

class ContactSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        // can't inject security context directly because of circular dependency for Doctrine entity manager
        $this->container = $container;
    }

    /**
     * @return SecurityContextInterface
     */
    protected function getSecurityContext()
    {
        if (!$this->securityContext) {
            $this->securityContext = $this->container->get('security.context');
        }

        return $this->securityContext;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array('prePersist', 'preUpdate');
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$this->isContactEntity($entity)) {
            return;
        }

        /** @var Contact $entity */
        $this->setCreatedProperties($entity);
        $this->setUpdatedProperties($entity);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$this->isContactEntity($entity)) {
            return;
        }

        /** @var Contact $entity */
        $this->setUpdatedProperties($entity);
    }

    /**
     * @param $entity
     * @return bool
     */
    protected function isContactEntity($entity)
    {
        return $entity instanceof Contact;
    }

    /**
     * @return User|null
     * @throws \LogicException
     */
    protected function getUser()
    {
        $token = $this->getSecurityContext()->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();
        if (!$user) {
            return null;
        }

        return $user;
    }

    /**
     * @param Contact $contact
     */
    protected function setCreatedProperties(Contact $contact)
    {
        $contact->setCreatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $contact->setCreatedBy($this->getUser());
    }

    /**
     * @param Contact $contact
     */
    protected function setUpdatedProperties(Contact $contact)
    {
        $contact->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $contact->setUpdatedBy($this->getUser());
    }
}
