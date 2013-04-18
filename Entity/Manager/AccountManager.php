<?php

namespace Oro\Bundle\AccountBundle\Entity\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\AccountBundle\Entity\Account;

class AccountManager
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
     * @return Account
     */
    public function createAccount()
    {
        $class = $this->getClass();
        return new $class;
    }

    /**
     * Updates an account
     *
     * @param Account $account
     * @param bool $flush Whether to flush the changes (default true)
     * @throws \RuntimeException
     */
    public function updateAccount(Account $account, $flush = true)
    {
        $this->getStorageManager()->persist($account);
        if ($flush) {
            $this->getStorageManager()->flush();
        }
    }

    /**
     * Deletes an account
     *
     * @param Account $account
     */
    public function deleteAccount(Account $account)
    {
        $this->getStorageManager()->remove($account);
        $this->getStorageManager()->flush();
    }

    /**
     * Finds one account by the given criteria
     *
     * @param  array $criteria
     * @return Account
     */
    public function findAccountBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Returns a collection with all account instances
     *
     * @return \Traversable
     */
    public function findAccounts()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * Reloads an account
     *
     * @param Account $account
     */
    public function reloadAccount(Account $account)
    {
        $this->getStorageManager()->refresh($account);
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
     * Returns basic query instance to get collection with all account instances
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQuery()
    {
        return $this->getStorageManager()
            ->createQueryBuilder()
            ->select('a')
            ->from('OroAccountBundle:Account', 'a')
            ->orderBy('a.id', 'ASC');
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
