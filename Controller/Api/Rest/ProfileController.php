<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
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
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page number, starting from 1. Optional, defaults to 1.")
     * @QueryParam(name="limit", requirements="\d+", default="10", description="Number of items per page. Optional, defaults to 10.")
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
     * @QueryParam(name="id", requirements="\d+", description="User id")
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
        $view   = $this->get('oro_user.form.handler.profile.api')->process($entity)
            ? RouteRedirectView::create('oro_api_get_user', array('id' => $entity->getId()))
            : $this->view($this->get('oro_user.form.profile.api'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * Update existing user
     *
     * @QueryParam(name="id", requirements="\d+", description="User id")
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
        $entity = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$entity) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $view = $this->get('oro_user.form.handler.profile')->process($entity)
            ? RouteRedirectView::create('oro_api_get_user', array('id' => $entity->getId()))
            : $this->view('', Codes::HTTP_INTERNAL_SERVER_ERROR);

        return $this->handleView($view);
    }

    /**
     * Delete user
     *
     * @QueryParam(name="id", requirements="\d+", description="User id")
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
     * @QueryParam(name="id", requirements="\d+", description="User id")
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
     * @QueryParam(name="id", requirements="\d+", description="User id")
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
     * @return Oro\Bundle\UserBundle\Entity\UserManager
     */
    protected function getManager()
    {
        return $this->get('oro_user.manager');
    }
}