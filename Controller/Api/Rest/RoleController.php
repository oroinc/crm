<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\Annotations\QueryParam;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

/**
 * @NamePrefix("oro_api_")
 */
class RoleController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get the list of roles
     *
     * @ApiDoc(
     *      description="Get the list of roles",
     *      resource=true
     * )
     * @AclAncestor("oro_user_role_list")
     */
    public function cgetAction()
    {
        return $this->handleView(
            $this->view(
                $this->getManager()->getRepository('OroUserBundle:Role')->findAll(),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * Get role data
     *
     * @param int $id Role id
     *
     * @ApiDoc(
     *      description="Get role data",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @Acl(
     *      id="oro_user_role_show",
     *      name="View role",
     *      description="View role",
     *      parent="oro_user_role"
     * )
     */
    public function getAction($id)
    {
        $entity = $this->getManager()->find('OroUserBundle:Role', (int) $id);

        return $this->handleView(
            $this->view(
                $entity,
                $entity ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
            )
        );
    }

    /**
     * Create new role
     *
     * @ApiDoc(
     *      description="Create new role",
     *      resource=true
     * )
     * @AclAncestor("oro_user_role_create")
     */
    public function postAction()
    {
        $entity = new Role();
        $view   = $this->get('oro_user.form.handler.role.api')->process($entity)
            ? $this->redirectView(
                $this->generateUrl('oro_api_get_role', array('id' => $entity->getId())),
                Codes::HTTP_CREATED
            )
            : $this->view($this->get('oro_user.form.role.api'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Update existing role
     *
     * @param int $id Role id
     *
     * @ApiDoc(
     *      description="Update existing role",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_role_edit")
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
     *
     * @ApiDoc(
     *      description="Delete role",
     *      resource=true,
     *      filters={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @Acl(
     *      id="oro_user_role_remove",
     *      name="Remove role",
     *      description="Remove role",
     *      parent="oro_user_role"
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
     *
     * @ApiDoc(
     *      description="Get role by name",
     *      resource=true,
     *      filters={
     *          {"name"="name", "dataType"="string"},
     *      }
     * )
     * @AclAncestor("oro_user_role_show")
     */
    public function getBynameAction($name)
    {
        $entity = $this->getManager()->getRepository('OroUserBundle:Role')->findOneBy(array('role' => $name));

        return $this->handleView(
            $this->view(
                $entity,
                $entity ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
            )
        );
    }

    /**
     * Get ACL resources granted by a role
     *
     * @param int $id User id
     *
     * @ApiDoc(
     *      description="Get ACL resources granted by a role",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("oro_user_acl_edit")
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
     * Link ACL resource to role
     *
     * @param int    $id       Role id
     * @param string $resource ACL resource id
     *
     * @ApiDoc(
     *      description="Link ACL resource to role",
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *          {"name"="resource", "dataType"="string"},
     *      }
     * )
     * @AclAncestor("oro_user_acl_save")
     */
    public function postAclAction($id, $resource)
    {
        $this->get('oro_user.acl_manager')->modifyAclForRole($id, $resource, true);

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * Unlink ACL resource from role
     *
     * @param int    $id       Role id
     * @param string $resource ACL resource id
     *
     * @ApiDoc(
     *      description="Unlink ACL resource from role",
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *          {"name"="resource", "dataType"="string"},
     *      }
     * )
     * @AclAncestor("oro_user_acl_save")
     */
    public function deleteAclAction($id, $resource)
    {
        $this->get('oro_user.acl_manager')->modifyAclForRole($id, $resource, false);

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * Link ACL resources to role
     *
     * @param int $id Role id
     *
     * @QueryParam(name="resources", nullable=false, description="Array of ACL resource ids")
     * @ApiDoc(
     *      description="Link ACL resources to role",
     *      requirements={
     *          {"name"="id", "dataType"="integer"}
     *      }
     * )
     * @AclAncestor("oro_user_acl_save")
     */
    public function postAclArrayAction($id)
    {
        $this->container->get('oro_user.acl_manager')->modifyAclsForRole(
            $id,
            $this->getRequest()->request->get('resources'),
            true
        );

        return $this->handleView($this->view('', Codes::HTTP_CREATED));
    }

    /**
     * Unlink ACL resources from role
     *
     * @param int $id Role id
     *
     * @QueryParam(name="resources", nullable=false, description="Array of ACL resource ids")
     * @ApiDoc(
     *      description="Unlink ACL resources from roles",
     *      requirements={
     *          {"name"="id", "dataType"="integer"}
     *      }
     * )
     * @AclAncestor("oro_user_acl_save")
     */
    public function deleteAclArrayAction($id)
    {
        $this->container->get('oro_user.acl_manager')->modifyAclsForRole(
            $id,
            $this->getRequest()->request->get('resources'),
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
