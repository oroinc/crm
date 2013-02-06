<?php
namespace Oro\Bundle\UserBundle\Aop;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Acl;
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
     * @var string
     */
    protected $cacheDir;

    public function __construct(ContainerInterface $container, $cacheDir)
    {
        $this->em = $container->get('doctrine')->getManager();
        $this->container = $container;
        $this->aclReader = $container->get('oro_user.acl_reader');
        $this->cacheDir = $cacheDir;
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
        $fs = new Filesystem();
        $fileName = $this->cacheDir . '/' . $aclId . '.json';

        if (!$fs->exists($fileName)) {
            if (!$fs->exists($this->cacheDir)) {
                $fs->mkdir($this->cacheDir);
            }

            //get roles from current resource and parents
            $accessRoles = $this->getRolesFromAclArray($this->getAclNodePath($aclId));

            $aclResource = $this->getAclRepo()->find($aclId);
            //get roles from resource children
            if ($this->getAclRepo()->childCount($aclResource)) {
                $accessRoles = array_unique(
                    array_merge(
                        $this->getRolesFromAclArray(
                            $this->getAclRepo()->children($aclResource)
                        ),
                        $accessRoles
                    )
                );
            }

            file_put_contents($fileName, json_encode($accessRoles));

        } else {
            $accessRoles = json_decode(file_get_contents($fileName));
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

        $aclRepo = $this->getAclRepo();
        $fs = new Filesystem();
        $fs->remove($this->cacheDir);

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
     * Get Acl for role
     *
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     *
     * @return array
     */
    public function getRoleAcl(Role $role)
    {
        return $this->getAclRepo()->getRoleAcl($role);
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
                $this->em->remove($bdResource);
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
     * @param \Oro\Bundle\UserBundle\Entity\Acl[] $data
     *
     * @return array
     */
    private function getRolesFromAclArray(array $data)
    {
        $accessRoles = array();
        foreach ($data as $acl) {
            /** @var \Oro\Bundle\UserBundle\Entity\Acl $acl */
            $roles = $acl->getAccessRolesNames();
            $accessRoles = array_unique(array_merge($roles, $accessRoles));
        }

        return $accessRoles;
    }
}