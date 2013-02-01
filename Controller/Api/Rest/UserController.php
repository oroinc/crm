<?php

namespace Oro\Bundle\UserBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Request;

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
class UserController extends FOSRestController implements ClassResourceInterface
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
                ->createQuery('SELECT u FROM OroUserBundle:User u'),
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
        $data = $this->getManager()->findUserBy(array('id' => (int) $id));

        return $this->handleView($this->view(
            $data,
            $data ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND
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
//        $user  = $this->get('oro_user.flexible_manager')->createFlexible();
        $user    = $this->getManager()->createUser();
        $request = $this->getRequest();
        $form    = $this->createForm('oro_user_form', $user, array('csrf_protection' => false));

        $form->bind($request);

        if ($form->isValid()) {
            var_dump($form);
            $this->getManager()->updateUser($user);

            $view = RouteRedirectView::create('oro_api_get_user', array('id' => $user->getId()));
        } else {
            $view = $this->view('', Codes::HTTP_INTERNAL_SERVER_ERROR);
        }

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
        $data = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$data) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }
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
        $data = $this->getManager()->findUserBy(array('id' => (int) $id));

        if (!$data) {
            return $this->handleView($this->view('', Codes::HTTP_NOT_FOUND));
        }

        $manager = $this->getDoctrine()->getEntityManager();

        $manager->remove($data);
        $manager->flush();

        return $this->handleView($this->view('', Codes::HTTP_NO_CONTENT));
    }

    /**
     * @return Oro\Bundle\UserBundle\Entity\UserManager
     */
    protected function getManager()
    {
        return $this->get('oro_user.user_manager');
    }
}