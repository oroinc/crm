<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

class RoleController extends BaseController
{
    /**
     * @Soap\Method("getRoles")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Role[]")
     * @AclAncestor("oro_user_role_list")
     */
    public function cgetAction()
    {
        return $this->getManager()->getRepository('OroUserBundle:Role')->findAll();
    }

    /**
     * @Soap\Method("getRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Role")
     * @AclAncestor("oro_user_role_show")
     */
    public function getAction($id)
    {
        return $this->getEntity('OroUserBundle:Role', $id);
    }

    /**
     * @Soap\Method("createRole")
     * @Soap\Param("role", phpType="Oro\Bundle\UserBundle\Entity\Role")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("oro_user_role_create")
     */
    public function createAction($role)
    {
        $entity = new Role();
        $form   = $this->container->get('oro_user.form.role.api');

        $this->container->get('oro_soap.request')->fix($form->getName());

        return $this->processForm($form->getName(), $entity);
    }

    /**
     * @Soap\Method("updateRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("role", phpType="Oro\Bundle\UserBundle\Entity\Role")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("oro_user_role_update")
     */
    public function updateAction($id, $role)
    {
        $entity = $this->getEntity('OroUserBundle:Role', $id);
        $form   = $this->container->get('oro_user.form.role.api');

        $this->container->get('oro_soap.request')->fix($form->getName());

        return $this->processForm($form->getName(), $entity);
    }

    /**
     * @Soap\Method("deleteRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("oro_user_role_remove")
     */
    public function deleteAction($id)
    {
        $em     = $this->getManager();
        $entity = $this->getEntity('OroUserBundle:Role', $id);

        $em->remove($entity);
        $em->flush();

        return true;
    }

    /**
     * @Soap\Method("getRoleByName")
     * @Soap\Param("name", phpType="string")
     * @Soap\Result(phpType="Oro\Bundle\UserBundle\Entity\Role")
     * @AclAncestor("oro_user_role_show")
     */
    public function getBynameAction($name)
    {
        $entity = $this->getManager()->getRepository('OroUserBundle:Role')->findOneBy(array('role' => $name));

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('Role "%s" can not be found', $name));
        }

        return $entity;
    }

    /**
     * @Soap\Method("getRoleAcl")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "string[]")
     * @AclAncestor("oro_user_acl_edit")
     */
    public function getAclAction($id)
    {
        $role = $this->getManager()->find('OroUserBundle:Role', (int) $id);
        if (!$role) {
            throw new \SoapFault('NOT_FOUND', sprintf('Role with id "%s" can not be found', $id));
        }

        return $this->container->get('oro_user.acl_manager')->getAllowedAclResourcesForRoles(array($role));
    }

    /**
     * Link ACL resource to role
     *
     * @param int    $id       Role id
     * @param string $resource ACL resource id
     *
     * @Soap\Method("addAclToRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("resource", phpType="string")
     * @Soap\Result(phpType="string")
     * @AclAncestor("oro_user_acl_save")
     */
    public function postAclAction($id, $resource)
    {
        $this->container->get('oro_user.acl_manager')->modifyAclForRole($id, $resource, true);

        return '';
    }

    /**
     * Unlink ACL resource from role
     *
     * @param int    $id       Role id
     * @param string $resource ACL resource id
     *
     * @Soap\Method("removeAclFromRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("resource", phpType="string")
     * @Soap\Result(phpType="string")
     * @AclAncestor("oro_user_acl_save")
     */
    public function deleteAclAction($id, $resource)
    {
        $this->container->get('oro_user.acl_manager')->modifyAclForRole($id, $resource, false);

        return '';
    }

    /**
     * Link ACL resources to role
     *
     * @param int   $id        Role id
     * @param array $resources Array of ACL resource ids
     *
     * @Soap\Method("addAclsToRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("resources", phpType="string[]")
     * @Soap\Result(phpType="string")
     * @AclAncestor("oro_user_acl_save")
     */
    public function addAclsToRoleAction($id, $resources)
    {
        $this->container->get('oro_user.acl_manager')->modifyAclsForRole($id, $resources, true);

        return '';
    }

    /**
     * Unlink ACL resources from role
     *
     * @param int   $id        Role id
     * @param array $resources Array of ACL resource ids
     *
     * @Soap\Method("removeAclsFromRole")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("resources", phpType="string[]")
     * @Soap\Result(phpType="string")
     * @AclAncestor("oro_user_acl_save")
     */
    public function deleteAclsAction($id, $resources)
    {
        $this->container->get('oro_user.acl_manager')->modifysForRole($id, $resources, false);

        return '';
    }
}
