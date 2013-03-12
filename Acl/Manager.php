<?php
namespace Oro\Bundle\UserBundle\Acl;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\UserBundle\Acl\ResourceReader\Reader;
use Oro\Bundle\UserBundle\Acl\ResourceReader\ConfigReader;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Acl;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Annotation\Acl as AnnotationAcl;

class Manager
{
    const ACL_ANNOTATION_CLASS = 'Oro\Bundle\UserBundle\Annotation\Acl';
    const ACL_ANCESTOR_ANNOTATION_CLASS = 'Oro\Bundle\UserBundle\Annotation\AclAncestor';

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $em;

    protected $securityContext;

    /**
     * @var \Oro\Bundle\UserBundle\Acl\ResourceReader\Reader
     */
    protected $aclReader;

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    protected $cache;

    /**
     * @var \Oro\Bundle\UserBundle\Acl\ResourceReader\ConfigReader
     */
    protected $configReader;

    public function __construct(
        ObjectManager $em,
        Reader $aclReader,
        CacheProvider $cache,
        SecurityContextInterface $securityContext,
        ConfigReader $configReader
    )
    {
        $this->em = $em;
        $this->aclReader = $aclReader;
        $this->cache = $cache;
        $this->cache->setNamespace('oro_user.cache');
        $this->securityContext = $securityContext;
        $this->configReader = $configReader;
    }

    /**
     * Check permissions for resource for user.
     *
     * @param                                    $aclResourceId
     * @param \Oro\Bundle\UserBundle\Entity\User $user
     *
     * @return bool
     */
    public function isResourceGranted($aclResourceId, User $user = null)
    {
        return $this->checkIsGrant(
            $this->getUserRoles($user),
            $this->getAclRoles($aclResourceId)
        );
    }

    /**
     * @param string                             $class
     * @param string                             $method
     * @param \Oro\Bundle\UserBundle\Entity\User $user
     *
     * @return bool
     */
    public function isClassMethodGranted($class, $method, User $user = null)
    {
        $acl = $this->getAclRepo()->findOneBy(
            array(
                 'class' => $class,
                 'method' => $method
            )
        );
        // todo: decide what to return if the resource is not found
        if (!$acl) {
            $accessRoles = $this->getAclRolesWithoutTree(Acl::ROOT_NODE);
        } else {
            $accessRoles = $this->getRolesForAcl($acl);
        }

        return $this->checkIsGrant(
            $this->getUserRoles($user),
            $accessRoles
        );
    }

    /**
     * Get roles for acl id
     *
     * @param $aclId
     * @return array
     */
    public function getAclRolesWithoutTree($aclId)
    {
        $roles = $this->cache->fetch('acl-roles-' . $aclId);
        if ($roles === false) {
            $roles = $this->getAclRepo()->getAclRolesWithoutTree($aclId);
            $this->cache->save('acl-roles-' . $aclId, $roles);
        }

        return $roles;
    }

    /**
     * get array of resource ids allowed for user
     *
     * @param \Oro\Bundle\UserBundle\Entity\User $user
     * @param bool                               $useObjects
     * @return array|\Oro\Bundle\UserBundle\Entity\Role[]
     */
    public function getAclForUser(User $user, $useObjects = false)
    {
        if ($useObjects) {
            $acl = $this->getAclRepo()->getAllowedAclResourcesForUserRoles($user->getRoles(), true);
        } else {
            $cachePrefix =  'user-acl-' . $user->getId();
            $acl = $this->cache->fetch($cachePrefix);
            if ($acl === false) {
                $acl = $this->getAclRepo()->getAllowedAclResourcesForUserRoles($user->getRoles());
                $this->cache->save($cachePrefix, $acl);
            }
        }

        return $acl;
    }

    /**
     * Get roles for ACL resource from cache. If cache file does not exists - create new one.
     *
     * @param string $aclId
     *
     * @return array
     */
    public function getAclRoles($aclId)
    {
        $accessRoles = $this->cache->fetch($aclId);
        if ($accessRoles === false) {
            $accessRoles = $this->getRolesForAcl(
                $this->getAclRepo()->find($aclId)
            );
            $this->cache->save($aclId, $accessRoles);
        }

        return $accessRoles;
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
        $resources = $this->getAclResources();
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
                $resources = $this->setResourceParent($resources, $parentResource);
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
     * @param \Oro\Bundle\UserBundle\Entity\Acl $acl
     *
     * @return array
     */
    private function getRolesForAcl(Acl $acl = null)
    {
        $accessRoles = array();
        if ($acl) {
            $aclNodes = $this->getAclRepo()->getFullNodeWithRoles($acl);
            foreach ($aclNodes as $node) {
                $roles = $node->getAccessRolesNames();
                $accessRoles = array_unique(array_merge($roles, $accessRoles));
            }
        }

        return $accessRoles;
    }

    /**
     * Get user roles
     * If user was not set in parameters, then user takes from Security Context.
     * If user was not logged and was not set in parameters, then return IS_AUTHENTICATED_ANONYMOUSLY role
     *
     * @param \Oro\Bundle\UserBundle\Entity\User $user
     *
     * @return array
     */
    private function getUserRoles(User $user = null)
    {
        if (null === $user) {
            $user = $this->getUser();
        }
        if ($user) {
            $roles = $this->cache->fetch('user-' . $user->getId());
            if ($roles === false) {
                $rolesObjects = $user->getRoles();
                foreach ($rolesObjects as $role) {
                    $roles[] = $role->getRole();
                }
                $this->cache->save('user-' . $user->getId(), $roles);
            }

        } else {
            $roles = array(User::ROLE_ANONYMOUS);
        }

        return $roles;
    }

    /**
     * Get a user from the Security Context
     *
     * @return mixed
     *
     * @see Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
     */
    private function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * Check is grant access for roles to the acl resource roles
     * @param array $roles
     * @param array $aclRoles
     *
     * @return bool
     */
    private function checkIsGrant(array $roles, array $aclRoles)
    {
        foreach ($roles as $role) {
            if (in_array($role, $aclRoles)) {
                return true;
            }
        }

        return false;
    }

    private function getAclResources()
    {
        $resourcesFromAnnotations = $this->aclReader->getResources();
        $resourcesFromConfigs = $this->configReader->getConfigResources();

        return $resourcesFromAnnotations + $resourcesFromConfigs;
    }
}
