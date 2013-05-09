<?php

namespace Oro\Bundle\SoapBundle\Entity\Manager;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

class ApiEntityManager
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
     * @var ClassMetadata
     */
    private $metadata;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param FlexibleManager $fm Proxy for methods of flexible manager
     */
    public function __construct($class, ObjectManager $om, FlexibleManager $fm)
    {
        $this->metadata = $om->getClassMetadata($class);

        $this->class = $this->metadata->getName();
        $this->om = $om;
        $this->fm = $fm;
    }

    /**
     * Return related repository
     *
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->getObjectManager()->getRepository($this->class);
    }

    /**
     * Retrieve object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->om;
    }

    /**
     * @return FlexibleManager
     */
    public function getFlexibleManager()
    {
        return $this->fm;
    }

    /**
     * Returns basic query instance to get collection with all user instances
     *
     * @param int $limit
     * @param int $offset
     * @return Paginator
     */
    public function getListQuery($limit = 10, $offset = 1)
    {
        $ids = $this->metadata->getIdentifierFieldNames();
        $orderBy = $ids ? array() : null;
        foreach ($ids as $pk) {
            $orderBy[$pk] = 'ASC';
        }
        /** @var FlexibleEntityRepository $repository */
        $repository = $this->getFlexibleManager()->getFlexibleRepository();
        return $repository->findByWithAttributesQB(array(), null, $orderBy, $limit, $offset);
    }
}
