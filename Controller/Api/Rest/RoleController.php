<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
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
            $this->getManager()
                ->createQuery('SELECT r FROM OroUserBundle:Role r')
                ->getArrayResult(),
            Codes::HTTP_OK
        ));
    }

    /**
     * Get role data
     *
     * @QueryParam(name="id", requirements="\d+", description="Role id")
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
     * @QueryParam(name="id", requirements="\d+", description="Role id")
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
     * @QueryParam(name="id", requirements="\d+", description="Role id")
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
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getEntityManagerForClass('OroUserBundle:Role');
    }
}
