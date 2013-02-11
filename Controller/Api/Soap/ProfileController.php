<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Soap;

use Symfony\Component\DependencyInjection\ContainerAware;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

class ProfileController extends ContainerAware
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
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('The group #%u can not be found', $id));
        }

        return $this->container->get('besimple.soap.response')->setReturnValue($entity);
    }

    /**
     * @Soap\Method("deleteUser")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
     */
    public function deleteAction($id)
    {
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('The user #%u can not be found', $id));
        }

        $this->getManager()->deleteUser($entity);

        return $this->container->get('besimple.soap.response')->setReturnValue(true);
    }

    /**
     * @Soap\Method("getUserRoles")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Role[]")
     */
    public function getRolesAction($id)
    {
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('The user #%u can not be found', $id));
        }

        return $this->container->get('besimple.soap.response')->setReturnValue($entity->getRoles());
    }

    /**
     * @Soap\Method("getUserGroups")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\UserBundle\Entity\Group[]")
     */
    public function getGroupsAction($id)
    {
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            throw new \SoapFault('NOT_FOUND', sprintf('The user #%u can not be found', $id));
        }

        return $this->container->get('besimple.soap.response')->setReturnValue($entity->getGroups());
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\UserManager
     */
    protected function getManager()
    {
        return $this->container->get('oro_user.manager');
    }
}