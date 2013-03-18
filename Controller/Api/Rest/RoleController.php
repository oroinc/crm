<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\UserBundle\Entity\Role;

/**
 * @NamePrefix("oro_api_")
 */
class RoleController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get the list of roles
     *
     * @ApiDoc(
     *  description="Get the list of roles",
     *  resource=true
     * )
     */
    public function cgetAction()
    {
        return $this->handleView($this->view(
            $this->getManager()->getRepository('OroUserBundle:Role')->findAll(),
            Codes::HTTP_OK
        ));
    }

    /**
     * Get role data
     *
     * @param int $id Role id
     * @ApiDoc(
     *  description="Get role data",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function getAction($id)
    {
        $entity = $this->getManager()->find('OroUserBundle:Role', (int) $id);

        return $this->handleView($this->view(
            $entity,
            $entity ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
        ));
    }

    /**
     * Create new role
     *
     * @ApiDoc(
     *  description="Create new role",
     *  resource=true
     * )
     */
    public function postAction()
    {
        $entity = new Role();
        $view   = $this->get('oro_user.form.handler.role.api')->process($entity)
            ? $this->redirectView($this->generateUrl('oro_api_get_role', array('id' => $entity->getId())), Codes::HTTP_CREATED)
            : $this->view($this->get('oro_user.form.role.api'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Update existing role
     *
     * @param int $id Role id
     * @ApiDoc(
     *  description="Update existing role",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function putAction($id)
    {
        $entity = $this->getManager()->find('OroUserBundle:Role', (int) $id);

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $view = $this->get('oro_user.form.handler.role.api')->process($entity)
            ? $this->redirectView($this->generateUrl('oro_api_get_role', array('id' => $entity->getId())))
            : $this->view($this->get('oro_user.form.role.api'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Delete role
     *
     * @param int $id Role id
     * @ApiDoc(
     *  description="Delete role",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function deleteAction($id)
    {
        $em     = $this->getManager();
        $entity = $em->find('OroUserBundle:Role', (int) $id);

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $em->remove($entity);
        $em->flush();

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * Get role by name
     *
     * @param string $name Role name
     * @ApiDoc(
     *  description="Get role by name",
     *  resource=true,
     *  filters={
     *      {"name"="name", "dataType"="string"},
     *  }
     * )
     */
    public function getBynameAction($name)
    {
        $entity = $this->getManager()->getRepository('OroUserBundle:Role')->findOneBy(array('role' => $name));

        return $this->handleView($this->view(
            $entity,
            $entity ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
        ));
    }

    /**
     * Get role acl list
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Get role allowed ACL resources",
     *  resource=true,
     *  requirements={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function getAclAction($id)
    {
        $role = $this->getManager()->find('OroUserBundle:Role', (int) $id);

        if (!$role) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView(
            $this->view(
                $this->get('oro_user.acl_manager')->getAllowedAclResourcesForRoles(array($role)),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * Link ACL Resource to role
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Link ACL Resource to role",
     *  requirements={
     *      {"roleId"="id", "dataType"="integer"},
     *      {"aclResourceId"="id", "dataType"="string"},
     *  }
     * )
     */
    public function postAclAction($roleId, $aclResourceId)
    {
        $this->get('oro_user.acl_manager')->modifyAclForRole(
            $roleId,
            $aclResourceId,
            true
        );

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * Create new role
     *
     * @ApiDoc(
     *  description="Create new role",
     *  resource=true
     * )
     */
    public function postAclsAction()
    {
        $entity = new Role();
        $view   = $this->get('oro_user.form.handler.role.api')->process($entity)
            ? $this->redirectView($this->generateUrl('oro_api_get_role', array('id' => $entity->getId())), Codes::HTTP_CREATED)
            : $this->view($this->get('oro_user.form.role.api'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Unlink ACL Resource to role
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Unlink ACL Resource to role",
     *  requirements={
     *      {"roleId"="id", "dataType"="integer"},
     *      {"aclResourceId"="id", "dataType"="string"},
     *  }
     * )
     */
    public function deleteAclAction($roleId, $aclResourceId)
    {
        $this->get('oro_user.acl_manager')->modifyAclForRole(
            $roleId,
            $aclResourceId,
            false
        );

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManagerForClass('OroUserBundle:Role');
    }
}
