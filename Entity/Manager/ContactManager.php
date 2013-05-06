<?php

namespace Oro\Bundle\ContactBundle\Entity\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\ContactBundle\Entity\Contact;

class ContactManager
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var FlexibleManager
     */
    protected $flexManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param FlexibleManager $flexManager Proxied flexible manager
     */
    public function __construct($class, ObjectManager $om, $flexManager)
    {
        $metadata = $om->getClassMetadata($class);

        $this->class          = $metadata->getName();
        $this->om             = $om;
        $this->flexManager    = $flexManager;
    }

    /**
     * Returns an empty user instance
     *
     * @return Contact
     */
    public function createContact()
    {
        $class = $this->getClass();
        return new $class;
    }

    /**
     * Updates an contact
     *
     * @param Contact $contact
     * @param bool $flush Whether to flush the changes (default true)
     * @throws \RuntimeException
     */
    public function updateContact(Contact $contact, $flush = true)
    {
        $this->getStorageManager()->persist($contact);
        if ($flush) {
            $this->getStorageManager()->flush();
        }
    }

    /**
     * Deletes an contact
     *
     * @param contact $contact
     */
    public function deleteContact(contact $contact)
    {
        $this->getStorageManager()->remove($contact);
        $this->getStorageManager()->flush();
    }

    /**
     * Finds one contact by the given criteria
     *
     * @param  array $criteria
     * @return contact
     */
    public function findContactBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Returns a collection with all contact instances
     *
     * @return \Traversable
     */
    public function findContacts()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * Reloads an contact
     *
     * @param contact $contact
     */
    public function reloadContact(Contact $contact)
    {
        $this->getStorageManager()->refresh($contact);
    }

    /**
     * Returns the user's fully qualified class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === $this->getClass();
    }

    /**
     * Returns basic query instance to get collection with all contact instances
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQuery()
    {
        return $this->getStorageManager()
            ->createQueryBuilder()
            ->select('c')
            ->from('OroContactBundle:Contact', 'c')
            ->orderBy('c.id', 'ASC');
    }

    /**
     * Return related repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository()
    {
        return $this->getStorageManager()->getRepository($this->getClass());
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getStorageManager()
    {
        return $this->om;
    }

    public function __call($name, $args)
    {
        if (method_exists($this->flexManager, $name)) {
            return call_user_func_array(array($this->flexManager, $name), $args);
        }

        throw new \RuntimeException(sprintf('Unknown method "%s"', $name));
    }
}
