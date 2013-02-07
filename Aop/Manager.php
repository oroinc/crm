<?php
namespace Oro\Bundle\UserBundle\Aop;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Acl;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Annotation\Acl as AnnotationAcl;

class Manager
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Oro\Bundle\UserBundle\ResourceReader\Reader
     */
    protected $aclReader;

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    protected $cache;

    public function __construct(ContainerInterface $container, $cacheDir)
    {
        $this->em = $container->get('doctrine')->getManager();
        $this->container = $container;
        $this->aclReader = $container->get('oro_user.acl_reader');
        $this->cache = $container->get('cache');
        $this->cache->setNamespace('oro_user.cache');
    }

    /**
     * get array of resource ids allowed for user
     *
     * @param \Oro\Bundle\UserBundle\Entity\User $user
     * @return array
     */
    public function getAclForUser(User $user)
    {
        return $this->getAclRepo()->getAllowedAclResourcesForUserRoles($user->getRoles());
    }

    /**
     * Get roles for ACL resource from cache. If cache file does not exists - create new one.
     *
     * @param string $aclId
     *
     * @return array
     */
    public function getCachedAcl($aclId)
    {
        $accessRoles = $this->cache->fetch($aclId);
        if ($accessRoles === false) {
            $accessRoles = $this->getRolesForAcl($aclId);
            $this->cache->save($aclId, $accessRoles);
        }

        return $accessRoles;
    }

    /**
     * Get Acl node path by node id
     *
     * @param string $aclId
     *
     * @return array
     */
    public function getAclNodePath($aclId)
    {
        return $this->getAclRepo()->getPathWithRoles($this->getAclRepo()->find($aclId));
    }

    /**
     * Save roles for ACL Resource
     *
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     * @param array                              $aclList
     */
    public function saveRoleAcl(Role $role, array $aclList = null)
    {
        $this->cache->deleteAll();

        $aclRepo = $this->getAclRepo();

        $aclCurrentList = $role->getAclResources();
        if ($aclCurrentList->count()) {
            foreach ($aclCurrentList as $acl) {
                $acl->removeAccessRole($role);
                $this->em->persist($acl);
            }
        }

        if (is_array($aclList) && count($aclList)) {
            foreach ($aclList as $aclId => $access) {
                /** @var $resource \Oro\Bundle\UserBundle\Entity\Acl */
                $resource = $aclRepo->find($aclId);
                $resource->addAccessRole($role);

                if ($resource->getParent() && $resource->getParent()->getId() !== 'root') {
                    $this->clearParentsAcl($resource->getParent(), $role);
                }
                $this->em->persist($resource);
            }
        }

        $this->em->flush();
    }

    /**
     * Get Acl tree for role
     *
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     *
     * @return array
     */
    public function getRoleAclTree(Role $role)
    {
        return $this->getAclRepo()->getRoleAclTree($role);
    }

    /**
     * Get Acl list for role
     *
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     *
     * @return array
     */
    public function getRoleAcl(Role $role)
    {
        return $this->getAclRepo()->getAclListWithRoles($role);
    }

    /**
     * Synchronize acl resources from db with resources from annotations
     */
    public function synchronizeAclResources()
    {
        $resources = $this->aclReader->getResources();
        $bdResources = $this->getAclRepo()->findAll();

        // update old resources
        foreach ($bdResources as $num => $bdResource) {
            /** @var \Oro\Bundle\UserBundle\Entity\Acl $bdResource */
            if (isset($resources[$bdResource->getId()])) {
                $resource = $resources[$bdResource->getId()];
                $bdResource->setData($resource);
                $resources = $this->setResourceParent($resources, $bdResource);
                $this->em->persist($bdResource);
                unset($bdResources[$num]);
                unset($resources[$bdResource->getId()]);
            }
        }

        //delete resources
        if (count($bdResources)) {
            foreach ($bdResources as $bdResource) {
                if ($bdResource->getId() != 'root') {
                    $this->em->remove($bdResource);
                }
            }
        }

        //add new resources
        if (count($resources)) {
            $this->createNewResources($resources);
        }

        $this->em->flush();

    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\Repository\AclRepository
     */
    protected function getAclRepo()
    {
        return $this->em->getRepository('OroUserBundle:Acl');
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRoleAclRepo()
    {
        return $this->em->getRepository('OroUserBundle:RoleAcl');
    }

    /**
     * @param \Oro\Bundle\UserBundle\Entity\Acl  $resource
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     */
    private function clearParentsAcl(Acl $resource, Role $role)
    {
        $resource->removeAccessRole($role);
        $this->em->persist($resource);
        if ($resource->getParent() && $resource->getParent()->getId() !== 'root') {
            $this->clearParentsAcl($resource->getParent(), $role);
        }
    }

    /**
     * Create new db ACL Resources from array with ACL definition
     *
     * @param array $resources
     */
    private function createNewResources(array $resources)
    {
        $resource = reset($resources);

        $bdResource = $this->createResource($resource);
        $resources = $this->setResourceParent($resources, $bdResource);
        unset($resources[$bdResource->getId()]);
        if (count($resources)) {
            $this->createNewResources($resources);
        }
    }

    /**
     * Set a parent for db ACL resource
     *
     * @param array                             $resources
     * @param \Oro\Bundle\UserBundle\Entity\Acl $bdResource
     *
     * @return array
     */
    private function setResourceParent(array $resources, Acl $bdResource)
    {
        $resource = $resources[$bdResource->getId()];
        if (!$resource->getParent()) {
            $parentResource = $this->getAclRepo()->find('root');
        } else {
            $parentResource = $this->getAclRepo()->find($resource->getParent());
            if (!$parentResource && isset($resources[$resource->getParent()])) {
                $parentResource = $this->createResource($resources[$resource->getParent()]);
                unset($resources[$resource->getParent()]);
            }
        }
        $bdResource->setParent($parentResource);

        return $resources;
    }

    /**
     * Create new db ACL resource from annotation data
     *
     * @param \Oro\Bundle\UserBundle\Annotation\Acl $resource
     *
     * @return \Oro\Bundle\UserBundle\Entity\Acl
     */
    private function createResource(AnnotationAcl $resource)
    {
        $dbResource = new Acl();
        $dbResource->setId($resource->getId());
        $dbResource->setData($resource);
        $this->em->persist($dbResource);

        return $dbResource;
    }

    /**
     * @param string $aclId
     *
     * @return array
     */
    private function getRolesForAcl($aclId)
    {
        $accessRoles = array();
        $acl = $this->getAclRepo()->find($aclId);
        $aclNodes = $this->getAclRepo()->getFullNodeWithRoles($acl);
        foreach ($aclNodes as $node) {
            $roles = $node->getAccessRolesNames();
            $accessRoles = array_unique(array_merge($roles, $accessRoles));
        }

        return $accessRoles;
    }
}