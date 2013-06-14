<?php

namespace OroCRM\Bundle\ContactBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\FlexibleRestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiFlexibleEntityManager;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\AddressBundle\Entity\TypedAddress;

/**
 * @RouteResource("contact")
 * @NamePrefix("oro_api_")
 */
class ContactController extends FlexibleRestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @QueryParam(name="page", requirements="\d+", nullable=true, description="Page number, starting from 1. Defaults to 1.")
     * @QueryParam(name="limit", requirements="\d+", nullable=true, description="Number of items per page. defaults to 10.")
     * @ApiDoc(
     *      description="Get all contacts items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_list")
     * @return Response
     */
    public function cgetAction()
    {
        $page = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get contact item",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id Contact item id
     *
     * @ApiDoc(
     *      description="Update contact",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new contact
     *
     * @ApiDoc(
     *      description="Create new contact",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_create")
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete Contact",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_contact_delete",
     *      name="Delete contact",
     *      description="Delete contact",
     *      parent="orocrm_contact"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * Get entity Manager
     *
     * @return ApiFlexibleEntityManager
     */
    public function getManager()
    {
        return $this->get('orocrm_contact.contact.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('orocrm_contact.form.contact.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_contact.form.handler.contact.api');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPreparedItem($entity)
    {
        // convert addresses to plain array
        $addressData = array();
        /** @var $entity Contact */
        /** @var $address TypedAddress */
        foreach ($entity->getMultiAddress() as $address) {
            $addressArray = parent::getPreparedItem($address);
            $addressArray['type'] = $address->getType()->getType();
            $addressData[] = $addressArray;
        }

        $result = parent::getPreparedItem($entity);
        $result['addresses'] = $addressData;
        unset($result['multiAddress']);

        return $result;
    }

    /**
     * @param Contact $entity
     */
    protected function fixRequestAttributes($entity)
    {
        parent::fixRequestAttributes($entity);

        $requestVariable = $this->getForm()->getName();
        $request = $this->getRequest()->request;
        $data = $request->get($requestVariable, array());

        $data['multiAddress'] = !empty($data['addresses']) ? $data['addresses'] : array();
        unset($data['addresses']);

        $request->set($requestVariable, $data);
    }
}
