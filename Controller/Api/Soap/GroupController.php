<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\UserBundle\Entity\Group;

class GroupController extends BaseController
{
    /**
     * @Soap\Method("getGroups")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Group[]")
     */
    public function сgetAction()
    {
        return $this->container->get('besimple.soap.response')->setReturnValue(
             $this->getManager()
                ->createQuery('SELECT g FROM OroUserBundle:Group g ORDER BY g.id')
                ->getResult()
        );
    }

    /**
     * @Soap\Method("getGroup")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Group")
     */
    public function getAction($id)
    {
        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->getEntity('OroUserBundle:Group', $id)
        );
    }

    /**
     * @Soap\Method("createGroup")
     * @Soap\Param("group", phpType = "\Oro\Bundle\UserBundle\Entity\Group")
     * @Soap\Result(phpType = "boolean")
     */
    public function createAction($group)
    {
        $entity = new Group();

        $this->container->get('oro_soap.request')->fix($this->container->get('oro_user.form.group.api')->getName());

        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->container->get('oro_user.form.handler.group.api')->process($entity)
        );
    }

    /**
     * @Soap\Method("updateGroup")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("group", phpType = "\Oro\Bundle\UserBundle\Entity\Group")
     * @Soap\Result(phpType = "boolean")
     */
    public function updateAction($id, $group)
    {
        $entity = $this->getEntity('OroUserBundle:Group', $id);

        $this->container->get('oro_soap.request')->fix($this->container->get('oro_user.form.group.api')->getName());

        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->container->get('oro_user.form.handler.group.api')->process($entity)
        );
    }

    /**
     * @Soap\Method("deleteGroup")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
     */
    public function deleteAction($id)
    {
        $entity = $this->getEntity('OroUserBundle:Group', $id);

        $em->remove($entity);
        $em->flush();

        return $this->container->get('besimple.soap.response')->setReturnValue(true);
    }

    /**
     * @Soap\Method("getGroupRoles")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Role[]")
     */
    public function getRolesAction($id)
    {
        $entity = $this->getEntity('OroUserBundle:Group', $id);

        return $this->container->get('besimple.soap.response')->setReturnValue($entity->getRoles()->toArray());
    }
}
