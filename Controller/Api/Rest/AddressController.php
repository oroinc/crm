<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\View\RouteRedirectView;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\Rest\Util\Codes;
use Oro\Bundle\AddressBundle\Entity\Manager\AddressManager;
use Oro\Bundle\AddressBundle\Entity\Address;

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
        $postArray = $this->getRequest()->request->all();
        if (empty($postArray)) {
            return $this->handleView(
                $this->view(
                    array('message' => 'Wrong JSON inside POST body'),
                    Codes::HTTP_BAD_REQUEST
                )
            );
        }

        /** @var $entity \Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface */
        $entity = $this->getManager()->getRepository()->findOneById((int)$addressId);
        if (!$entity) {
            return $this->handleView($this->view(array(), Codes::HTTP_NOT_FOUND));
        }

        $this->fixFlexRequest($entity);

        $view = $this->get('oro_address.form.handler.address.api')->process($entity)
            ? $this->view(array(), Codes::HTTP_OK)
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
            ? RouteRedirectView::create('oro_api_get_address', array('id' => $entity->getId()), Codes::HTTP_CREATED)
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
        $em->remove($entity);
        $em->flush();

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
     * @param User $entity
     */
    protected function fixFlexRequest(Address $entity)
    {
        $request = $this->getRequest()->request;
        $data = $request->get('address', array());
        $attrDef = $this->getManager()->getAttributeRepository()->findBy(array('entityType' => get_class($entity)));
        $attrVal = isset($data['values']) ? $data['values'] : array();

        $data['values'] = array();

        foreach ($attrDef as $i => $attr) {
            /* @var $attr \Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttribute */
            if ($attr->getBackendType() == 'options') {
                if (in_array(
                    $attr->getAttributeType(),
                    array(
                        'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiSelectType',
                        'Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\OptionMultiCheckboxType',
                    ))
                ) {
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

            $data['values'][$i]        = array();
            $data['values'][$i]['id']  = $attr->getId();
            $data['values'][$i][$type] = $default;

            foreach ($attrVal as $field) {
                if ($attr->getCode() == (string) $field->code) {
                    $data['values'][$i][$type] = (string) $field->value;

                    break;
                }
            }
        }

        $request->set('address', $data);
    }
}
