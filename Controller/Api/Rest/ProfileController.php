<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * @NamePrefix("oro_api_")
 */
class ProfileController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get the list of users
     *
     * @param int $page  [optional] Page number, starting from 1. Defaults to 1.
     * @param int $limit [optional] Number of items per page. defaults to 10.
     * @ApiDoc(
     *  description="Get the list of users",
     *  resource=true,
     *  filters={
     *      {"name"="page", "dataType"="integer"},
     *      {"name"="limit", "dataType"="integer"}
     *  }
     * )
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        $pager = $this->get('knp_paginator')->paginate(
            $this->getDoctrine()
                ->getEntityManager()
                ->createQuery('SELECT u FROM OroUserBundle:User u ORDER BY u.id'),
            (int) $page,
            (int) $limit
        );

        return $this->handleView($this->view(
            $pager->getItems(),
            Codes::HTTP_OK
        ));
    }

    /**
     * Get user data
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Get user data",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function getAction($id)
    {
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        return $this->handleView($this->view(
            $entity,
            $entity ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
        ));
    }

    /**
     * Create new user
     *
     * @ApiDoc(
     *  description="Create new user",
     *  resource=true
     * )
     */
    public function postAction()
    {
        $entity = $this->getManager()->createFlexible();

        $this->fixFlexRequest($entity);

        $view = $this->get('oro_user.form.handler.profile.api')->process($entity)
            ? RouteRedirectView::create('oro_api_get_profile', array('id' => $entity->getId()), Codes::HTTP_CREATED)
            : $this->view($this->get('oro_user.form.profile.api'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Update existing user
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Update existing user",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function putAction($id)
    {
        /* @var $entity \Oro\Bundle\UserBundle\Entity\User */
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $this->fixFlexRequest($entity);

        $view = $this->get('oro_user.form.handler.profile.api')->process($entity)
            ? RouteRedirectView::create('oro_api_get_profile', array('id' => $entity->getId()), Codes::HTTP_NO_CONTENT)
            : $this->view($this->get('oro_user.form.profile.api'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Delete user
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Delete user",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function deleteAction($id)
    {
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $this->getManager()->deleteUser($entity);

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * Get user roles
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Get user roles",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function getRolesAction($id)
    {
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($entity->getRoles(), Codes::HTTP_OK));
    }

    /**
     * Get user groups
     *
     * @param int $id User id
     * @ApiDoc(
     *  description="Get user groups",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     */
    public function getGroupsAction($id)
    {
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($entity->getGroups(), Codes::HTTP_OK));
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\UserManager
     */
    protected function getManager()
    {
        return $this->get('oro_user.manager');
    }

    /**
     * This is temporary fix for flexible entity values processing.
     *
     * Assumed that user will post data in the following format:
     * {"profile":{"username":"john123","email":"john@doe.com","attributes":{"firstname":"John"}}}
     *
     * @param User $user
     */
    protected function fixFlexRequest(User $entity)
    {
        $request = $this->getRequest()->request;
        $data    = $request->get('profile', array());

        if (array_key_exists('attributes', $data)) {
            $attrs  = $this->getManager()->getAttributeRepository()->findBy(array('entityType' => get_class($entity)));
            $values = array();
            $i      = 0;

            // transform simple notation into FlexibleType format
            foreach ($data['attributes'] as $field => $value) {
                foreach ($attrs as $attr) {
                    if ($attr->getCode() == $field) {
                        $values[$i]['id']   = $attr->getId();
                        $values[$i]['data'] = $value;

                        $i++;
                    }
                }
            }

            $data['attributes'] = $values;

            $request->set('profile', $data);
        }
    }
}
