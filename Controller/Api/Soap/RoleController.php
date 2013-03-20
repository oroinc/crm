<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\UserBundle\Entity\Role;

class RoleController extends BaseController
{
    /**
     * @Soap\Method("getRoles")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Role[]")
     */
    public function cgetAction()
    {
        return $this->getManager()->getRepository('OroUserBundle:Role')->findAll();
    }

    /**
     * @Soap\Method("getRole")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Role")
     */
    public function getAction($id)
    {
        return $this->getEntity('OroUserBundle:Role', $id);
    }

    /**
     * @Soap\Method("createRole")
     * @Soap\Param("role", phpType = "Oro\Bundle\UserBundle\Entity\Role")
     * @Soap\Result(phpType = "boolean")
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
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("role", phpType = "Oro\Bundle\UserBundle\Entity\Role")
     * @Soap\Result(phpType = "boolean")
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
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
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
     * @Soap\Param("name", phpType = "string")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Role")
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
     * Link ACL Resource to role
     *
     * @Soap\Method("addAclToRole")
     * @Soap\Param("roleId", phpType = "int")
     * @Soap\Param("aclResourceId", phpType = "string")
     * @Soap\Result(phpType = "string")
     */
    public function postAclAction($roleId, $aclResourceId)
    {
        $this->container->get('oro_user.acl_manager')->modifyAclForRole(
            $roleId,
            $aclResourceId,
            true
        );

        return '';
    }

    /**
     * Link ACL Resource to role
     *
     * @Soap\Method("addAclsToRole")
     * @Soap\Param("roleId", phpType = "int")
     * @Soap\Param("aclResources", phpType = "string[]")
     * @Soap\Result(phpType = "string")
     */
    public function addAclsToRoleAction($roleId, $aclResources)
    {
        $this->container->get('oro_user.acl_manager')->modifyAclsForRole(
            $roleId,
            $aclResources,
            true
        );

        return '';
    }

    /**
     * Link ACL Resource to role
     *
     * @Soap\Method("removeAclsFromRole")
     * @Soap\Param("roleId", phpType = "int")
     * @Soap\Param("aclResources", phpType = "string[]")
     * @Soap\Result(phpType = "string")
     */
    public function removeAclsFromRoleAction($roleId, $aclResources)
    {
        $this->container->get('oro_user.acl_manager')->modifysForRole(
            $roleId,
            $aclResources,
            false
        );

        return '';
    }

    /**
     * Unlink ACL Resource from role
     *
     * @Soap\Method("removeAclFromRole")
     * @Soap\Param("roleId", phpType = "int")
     * @Soap\Param("aclResourceId", phpType = "string")
     * @Soap\Result(phpType = "string")
     */
    public function deleteAclAction($roleId, $aclResourceId)
    {
        $this->container->get('oro_user.acl_manager')->modifyAclForRole(
            $roleId,
            $aclResourceId,
            false
        );

        return '';
    }
}
