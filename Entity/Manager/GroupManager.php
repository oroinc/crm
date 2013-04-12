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
}
