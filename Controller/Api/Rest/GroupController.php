<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\UserBundle\Entity\Group;

/**
 * @NamePrefix("oro_api_")
 */
class GroupController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get the list of groups
     *
     * @ApiDoc(
     *  description="Get the list of groups",
     *  resource=true
     * )
     */
    public function cgetAction()
    {
        return $this->handleView(
            $this->view(
                $this->getManager()->getRepository('OroUserBundle:Group')->findAll(),
                Codes::HTTP_OK
            )
        );
    }

    /**
     * Get group data
     *
     * @param int $id Group id
     *
     * @ApiDoc(
     *  description="Get group data",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function getAction($id)
    {
        $entity = $this->getManager()->find('OroUserBundle:Group', (int) $id);

        return $this->handleView(
            $this->view(
                $entity,
                $entity ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
            )
        );
    }

    /**
     * Create new group
     *
     * @ApiDoc(
     *  description="Create new group",
     *  resource=true
     * )
     */
    public function postAction()
    {
        $entity = new Group();
        $view = $this->get('oro_user.form.handler.group.api')->process($entity)
            ? $this->redirectView(
                $this->generateUrl('oro_api_get_group', array('id' => $entity->getId())),
                Codes::HTTP_CREATED
            )
            : $this->view($this->get('oro_user.form.group.api'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Update existing group
     *
     * @param int $id Group id
     *
     * @ApiDoc(
     *  description="Update existing group",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function putAction($id)
    {
        $entity = $this->getManager()->find('OroUserBundle:Group', (int) $id);

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $view = $this->get('oro_user.form.handler.group.api')->process($entity)
            ? $this->redirectView($this->generateUrl('oro_api_get_group', array('id' => $entity->getId())))
            : $this->view($this->get('oro_user.form.group.api'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Delete group
     *
     * @param int $id Group id
     *
     * @ApiDoc(
     *  description="Delete group",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function deleteAction($id)
    {
        $em = $this->getManager();
        $entity = $em->find('OroUserBundle:Group', (int) $id);

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $em->remove($entity);
        $em->flush();

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * Get group roles
     *
     * @param int $id Group id
     *
     * @ApiDoc(
     *  description="Get group roles",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function getRolesAction($id)
    {
        $entity = $this->getManager()->find('OroUserBundle:Group', (int) $id);

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($entity->getRoles(), Codes::HTTP_OK));
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManagerForClass('OroUserBundle:Group');
    }
}
