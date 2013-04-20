<?php

namespace Oro\Bundle\AddressBundle\Entity\Manager;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

use Doctrine\Common\Persistence\ObjectManager;

/***
 * Class AddressManager
 * @package Oro\Bundle\AddressBundle\Entity\Manager
 *
 * @method string getFlexibleName()
 * @method string getFlexibleValueName()
 */
class AddressManager implements StorageInterface
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
    protected $fm;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param FlexibleManager $fm Proxy for methods of flexible manager
     */
    public function __construct($class, ObjectManager $om, $fm)
    {
        $metadata = $om->getClassMetadata($class);

        $this->class = $metadata->getName();
        $this->om = $om;
        $this->fm = $fm;
    }

    /**
     * Returns an empty address instance
     *
     * @return Address
     */
    public function createAddress()
    {
        $class = $this->getClass();

        return new $class;
    }

    /**
     * Updates an address
     *
     * @param Address $address
     * @param bool $flush Whether to flush the changes (default true)
     * @throws \RuntimeException
     */
    public function updateAddress(Address $address, $flush = true)
    {
        $this->getStorageManager()->persist($address);
        if ($flush) {
            $this->getStorageManager()->flush();
        }
    }

    /**
     * Deletes an address
     *
     * @param Address $address
     */
    public function deleteAddress(Address $address)
    {
        $this->getStorageManager()->remove($address);
        $this->getStorageManager()->flush();
    }

    /**
     * Finds one address by the given criteria
     *
     * @param  array $criteria
     * @return Address
     */
    public function findAddressBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Reloads an address
     *
     * @param Address $address
     */
    public function reloadAddress(Address $address)
    {
        $this->getStorageManager()->refresh($address);
    }

    /**
     * Returns the address's fully qualified class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
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
     * Retrieve object manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getStorageManager()
    {
        return $this->om;
    }

    /**
     * Returns basic query instance to get collection with all user instances
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getListQuery()
    {
        return $this->getStorageManager()
            ->createQueryBuilder()
            ->select('a')
            ->from('OroAddressBundle:Address', 'a')
            ->orderBy('a.id', 'ASC');
    }

    /**
     * Provide proxy method calls to flexible manager
     *
     * @param string $name
     * @param array $args
     * @return mixed
     * @throws \RuntimeException
     */
    public function __call($name, $args)
    {
        if (method_exists($this->fm, $name)) {
            return call_user_func_array(array($this->fm, $name), $args);
        }

        throw new \RuntimeException(sprintf('Unknown method "%s"', $name));
    }
}
