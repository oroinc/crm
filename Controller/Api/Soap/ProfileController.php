<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\UserBundle\Entity\UserSoap;

class ProfileController extends BaseController
{
    /**
     * @Soap\Method("getUsers")
     * @Soap\Param("page", phpType = "int")
     * @Soap\Param("limit", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\User[]")
     */
    public function сgetAction($page = 1, $limit = 10)
    {
        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->container->get('knp_paginator')->paginate(
                $this->container->get('doctrine.orm.entity_manager')
                    ->createQuery('SELECT u FROM OroUserBundle:User u ORDER BY u.id'),
                (int) $page,
                (int) $limit
            )
        );
    }

    /**
     * @Soap\Method("getUser")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\User")
     */
    public function getAction($id)
    {
        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->getEntity('OroUserBundle:User', $id)
        );
    }

    /**
     * @Soap\Method("createUser")
     * @Soap\Param("profile", phpType = "\Oro\Bundle\UserBundle\Entity\UserSoap")
     * @Soap\Result(phpType = "boolean")
     */
    public function createAction($profile)
    {
        $entity = $this->getUserManager()->createFlexible();

        $this->container->get('oro_soap.request')->fix($this->container->get('oro_user.form.profile.api')->getName());

        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->container->get('oro_user.form.handler.profile.api')->process($entity)
        );
    }

    /**
     * @Soap\Method("updateUser")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("profile", phpType = "\Oro\Bundle\UserBundle\Entity\UserSoap")
     * @Soap\Result(phpType = "boolean")
     */
    public function updateAction($id, $profile)
    {
        $entity = $this->getEntity('OroUserBundle:User', $id);

        $this->container->get('oro_soap.request')->fix($this->container->get('oro_user.form.profile.api')->getName());

        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->container->get('oro_user.form.handler.profile.api')->process($entity)
        );
    }

    /**
     * @Soap\Method("deleteUser")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
     */
    public function deleteAction($id)
    {
        $entity = $this->getEntity('OroUserBundle:User', $id);

        $this->getUserManager()->deleteUser($entity);

        return $this->container->get('besimple.soap.response')->setReturnValue(true);
    }

    /**
     * @Soap\Method("getUserRoles")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Role[]")
     */
    public function getRolesAction($id)
    {
        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->getEntity('OroUserBundle:User', $id)->getRoles()
        );
    }

    /**
     * @Soap\Method("getUserGroups")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Group[]")
     */
    public function getGroupsAction($id)
    {
        return $this->container->get('besimple.soap.response')->setReturnValue(
            $this->getEntity('OroUserBundle:User', $id)->getGroups()
        );
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\UserManager
     */
    protected function getUserManager()
    {
        return $this->container->get('oro_user.manager');
    }
}
