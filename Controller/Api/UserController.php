<?php

namespace Oro\Bundle\UserBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\RouteRedirectView;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * @NamePrefix("oro_api_")
 */
class UserController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *  description="Get user list",
     *  resource=true,
     *  filters={
     *      {"name"="page", "dataType"="integer"},
     *      {"name"="limit", "dataType"="integer"}
     *  }
     * )
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page number, starting from 1. Optional, defaults to 1.")
     * @QueryParam(name="limit", requirements="\d+", default="10", description="Number of items per page. Optional, defaults to 10.")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        $pager = $this->get('knp_paginator')->paginate(
            $this->getDoctrine()
                ->getEntityManager()
                ->createQuery('SELECT u FROM OroUserBundle:User u'),
            $page,
            $limit
        );

        return $this->handleView($this->view(
            $pager->getItems(),
            200
        ));
    }

    /**
     * @ApiDoc(
     *  description="Get user data",
     *  resource=true,
     *  filters={
     *      {"name"="id", "dataType"="integer"},
     *  }
     * )
     * @QueryParam(name="id", requirements="\d+", description="User id")
     */
    public function getAction($id)
    {
        $data = $this->get('oro_user.user_manager')->findUserBy(array('id' => (int) $id));

        return $this->handleView($this->view(
            $data,
            $data ? 200 : 404
        ));
    }

    /**
     * @ApiDoc(
     *  description="Process new user creation",
     *  resource=true
     * )
     */
    public function postAction(ParamFetcher $params)
    {
//        $form = $this->getForm();
//
//        $form->bind($request);
//
//        if ($form->isValid()) {
//            // Note: normally one would likely create/update something in the database
//            // and/or send an email and finally redirect to the newly created or updated resource url
//            // ...
//
//            $view = RouteRedirectView::create('oro', array('id' => 1));
//        } else {
            $view = $this->view('', 200);
//        }

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *  description="Update existing user",
     *  resource=true
     * )
     */
    public function putAction(ParamFetcher $params)
    {

    }
}