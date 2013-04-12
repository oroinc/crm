<?php

namespace Oro\Bundle\UserBundle\Entity\Manager;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\UserBundle\Entity\Role;

class RoleManager
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
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     */
    public function getUserQueryBuilder(Role $role)
    {
        return $this->getRoleRepo()->getUserQueryBuilder($role);
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\Repository\RoleRepository
     */
    protected function getRoleRepo()
    {
        return $this->em->getRepository('OroUserBundle:Role');
    }
}
