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
    public function сgetAction()
    {
        return $this->container->get('besimple.soap.response')->setReturnValue(
             $this->getManager()
                ->createQuery('SELECT r FROM OroUserBundle:Role r ORDER BY r.id')
                ->getResult()
        );
    }

    /**
     * @Soap\Method("getRole")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Role")
     */
    public function getAction($id)
    {
        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->getEntity('OroUserBundle:Role', $id)
        );
    }

    /**
     * @Soap\Method("createRole")
     * @Soap\Param("role", phpType = "\Oro\Bundle\UserBundle\Entity\Role")
     * @Soap\Result(phpType = "boolean")
     */
    public function createAction($role)
    {
        $entity = new Role();

        $this->fixRequest($this->container->get('oro_user.form.role.api')->getName());

        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->container->get('oro_user.form.handler.role.api')->process($entity)
        );
    }

    /**
     * @Soap\Method("updateRole")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("role", phpType = "\Oro\Bundle\UserBundle\Entity\Role")
     * @Soap\Result(phpType = "boolean")
     */
    public function updateAction($id, $role)
    {
        $entity = $this->getEntity('OroUserBundle:Role', $id);

        $this->fixRequest($this->container->get('oro_user.form.role.api')->getName());

        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->container->get('oro_user.form.handler.role.api')->process($entity)
        );
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

        return $this->container->get('besimple.soap.response')->setReturnValue(true);
    }
}
