<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\View\RouteRedirectView;
use Knp\Component\Pager\Paginator;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\AddressBundle\Entity\Manager\AddressManager;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute;

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

        /** @var Paginator $pager */
        $pager = $this->get('knp_paginator')->paginate(
            $addressManager->getListQuery()
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
        $addressManager = $this->getManager();
        $item = $addressManager->getRepository()->findOneById($id);

        return $this->handleView(
            $this->view($item, is_object($item) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * REST PUT
     *
     * @param int $addressId Address item id
     *
     * @ApiDoc(
     *  description="Update address",
     *  resource=true
     * )
     * @return Response
     */
    public function putAction($addressId)
    {
        $entity = $this->getManager()->getRepository()->findOneById((int)$addressId);
        if (!$entity) {
            return $this->handleView($this->view(array(), Codes::HTTP_NOT_FOUND));
        }

        $this->fixFlexRequest($entity);
        $view = $this->get('oro_address.form.handler.address.api')->process($entity)
            ? $this->view(array(), Codes::HTTP_NO_CONTENT)
            : $this->view($this->get('oro_address.form.address.api'), Codes::HTTP_BAD_REQUEST);


        return $this->handleView($view);
    }

    /**
     * Create new address
     *
     * @ApiDoc(
     *  description="Create new address",
     *  resource=true
     * )
     */
    public function postAction()
    {
        $entity = $this->getManager()->createFlexible();

        $this->fixFlexRequest($entity);

        $view = $this->get('oro_address.form.handler.address.api')->process($entity)
            ? $this->view(array('id' => $entity->getId()), Codes::HTTP_CREATED)
            : $this->view($this->get('oro_address.form.address.api'), Codes::HTTP_BAD_REQUEST);

        return $this->handleView($view);
    }

    /**
     * REST DELETE
     *
     * @param int $addressId
     *
     * @ApiDoc(
     *  description="Remove Address",
     *  resource=true
     * )
     * @return Response
     */
    public function deleteAction($addressId)
    {
        $entity = $this->getManager()->getRepository()->findOneById((int)$addressId);
        if (!$entity) {
            return $this->handleView($this->view(array(), Codes::HTTP_NOT_FOUND));
        }

        $em = $this->getManager();
        $em->deleteAddress($entity);

        return $this->handleView($this->view(array(), Codes::HTTP_NO_CONTENT));
    }

    /**
     * Get entity Manager
     *
     * @return AddressManager
     */
    protected function getManager()
    {
        return $this->get('oro_address.address.manager');
    }

    /**
     * This is temporary fix for flexible entity values processing.
     *
     * Assumed that user will post data in the following format:
     * {address: {"id": "21", "street":"Test","city":"York","values":{"firstname":"John"}}}
     *
     * @param Address $entity
     */
    protected function fixFlexRequest(Address $entity)
    {
        $request = $this->getRequest()->request;
        $data = $request->get('address', array());
        $attrDef = $this->getManager()->getAttributeRepository()->findBy(array('entityType' => get_class($entity)));
        $attrVal = isset($data['attributes']) ? $data['attributes'] : array();

        $data['attributes'] = array();

        foreach ($attrDef as $i => $attr) {
            /* @var $attr \Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute */
            if ($attr->getBackendType() == 'options') {
                if (in_array(
                    $attr->getAttributeType(),
                    array(
                        'oro_flexibleentity_multiselect',
                        'oro_flexibleentity_multicheckbox',
                    )
                )) {
                    $type    = 'options';
                    $default = array($attr->getOptions()->offsetGet(0)->getId());
                } else {
                    $type    = 'option';
                    $default = $attr->getOptions()->offsetGet(0)->getId();
                }
            } else {
                $type    = $attr->getBackendType();
                $default = null;
            }

            $data['attributes'][$i]        = array();
            $data['attributes'][$i]['id']  = $attr->getId();
            $data['attributes'][$i][$type] = $default;

            foreach ($attrVal as $field) {
                if ($attr->getCode() == (string) $field->code) {
                    $data['attributes'][$i][$type] = (string) $field->value;

                    break;
                }
            }
        }

        $request->set('address', $data);
    }
}
