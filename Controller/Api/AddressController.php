<?php

namespace Oro\Bundle\AddressBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\Rest\Util\Codes;
use Oro\Bundle\AddressBundle\Entity\Manager\AddressManager;


/**
 * @RouteResource("address")
 * @NamePrefix("oro_api_")
 */
class AddressController extends FOSRestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @QueryParam(name="page", requirements="\d+", nullable=true, description="Page number, starting from 1. Defaults to 1.")
     * @QueryParam(name="limit", requirements="\d+", nullable=true, description="Number of items per page. defaults to 10.")
     * @ApiDoc(
     *  description="Get all addresses items",
     *  resource=true
     * )
     * filters={
     *      {"name"="page", "dataType"="integer"},
     *      {"name"="limit", "dataType"="integer"}
     *  }
     * @return Response
     */
    public function cgetAction()
    {
        $addressManager = $this->getManager();
        //$items = $addressManager->getRepository()->findAll();

        $pager = $this->get('knp_paginator')->paginate(
            $addressManager->getRepository()
                ->createFlexibleQueryBuilder('address')
                ->getQuery()
                ->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY),
            (int) $this->getRequest()->get('page', 1),
            (int) $this->getRequest()->get('limit', 10)
        );

        $items = $pager->getItems();

        return $this->handleView(
            $this->view($items, is_array($items) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *  description="Get address item",
     *  resource=true
     * )
     * @return Response
     */
    public function getAction($id)
    {
        $addressManager = $this->container->get('oro_address.address.manager');
        $items = $addressManager->getRepository()->findAll();

        return $this->handleView(
            $this->view($items, is_array($items) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * Get entity Manager
     *
     * @return Oro\Bundle\AddressBundle\Entity\Manager\AddressManager
     */
    protected function getManager()
    {
        return $this->container->get('oro_address.address.manager');
    }
}
