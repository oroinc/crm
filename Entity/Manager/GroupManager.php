<?php

namespace Oro\Bundle\UserBundle\Entity\Manager;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\UserBundle\Entity\Group;

class GroupManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get user query builder
     *
     * @param \Oro\Bundle\UserBundle\Entity\Group $role
     */
    public function getUserQueryBuilder(Group $group)
    {
        return $this->getGroupRepo()->getUserQueryBuilder($group);
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\Repository\GroupRepository
     */
    protected function getGroupRepo()
    {
        return $this->em->getRepository('OroUserBundle:Group');
    }

    /**
     * Return group list
     *
     * @return \Oro\Bundle\UserBundle\Entity\Group[]
     */
    public function getList()
    {
        $this->em->getRepository('OroUserBundle:Group')->findAll();
    }

    /**
     * Return group entity
     *
     * @param $id
     * @return \Oro\Bundle\UserBundle\Entity\Group
     */
    public function find($id)
    {
        return $this->em->find('OroUserBundle:Group', $id);
    }

    public function createEntity()
    {
        return new $this->class;
    }

    public function getObjectManager()
    {
        return $this->em;
    }
}
