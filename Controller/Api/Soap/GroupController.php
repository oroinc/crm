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
    public function cgetAction()
    {
        return $this->getManager()->getRepository('OroUserBundle:Group')->findAll();
    }

    /**
     * @Soap\Method("getGroup")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Group")
     */
    public function getAction($id)
    {
        return $this->getEntity('OroUserBundle:Group', $id);
    }

    /**
     * @Soap\Method("createGroup")
     * @Soap\Param("group", phpType = "\Oro\Bundle\UserBundle\Entity\Group")
     * @Soap\Result(phpType = "boolean")
     */
    public function createAction($group)
    {
        $entity = new Group();
        $form   = $this->container->get('oro_user.form.group.api');

        $this->container->get('oro_soap.request')->fix($form->getName());

        return $this->processForm($form->getName(), $entity);
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
        $form   = $this->container->get('oro_user.form.group.api');

        $this->container->get('oro_soap.request')->fix($form->getName());

        return $this->processForm($form->getName(), $entity);
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

        return true;
    }

    /**
     * @Soap\Method("getGroupRoles")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Role[]")
     */
    public function getRolesAction($id)
    {
        $entity = $this->getEntity('OroUserBundle:Group', $id);

        return $entity->getRoles()->toArray();
    }
}
