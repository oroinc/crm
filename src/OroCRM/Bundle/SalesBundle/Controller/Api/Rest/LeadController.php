<?php

namespace OroCRM\Bundle\SalesBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use OroCRM\Bundle\SalesBundle\Entity\Lead;

/**
 * @RouteResource("lead")
 * @NamePrefix("oro_api_")
 */
class LeadController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET lead address
     *
     * @param string $leadId
     *
     * @ApiDoc(
     *      description="Get lead address",
     *      resource=true
     * )
     * @AclAncestor("orocrm_sales_lead_view")
     * @deprecated since 1.10. Use /api/rest/{version}/leads/{leadId}/addresses.{_format} instead.
     * @return Response
     */
    public function getAddressAction($leadId)
    {
        /** @var Lead $item */
        $item = $this->getManager()->find($leadId);

        $address = null;
        if ($item) {
            $addressEntity = $item->getPrimaryAddress();
            if ($addressEntity) {
                $address = $this->getPreparedItem($addressEntity);
                $address['countryIso2'] = $addressEntity->getCountryIso2();
                $address['countryIso3'] = $addressEntity->getCountryIso3();
                $address['regionCode'] = $addressEntity->getRegionCode();
                $address['country'] = $addressEntity->getCountryName();
            }
        }
        $responseData = $address ? json_encode($address) : '';

        return new Response($responseData, Codes::HTTP_OK);
    }

    /**
     * REST GET list
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+", nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get all lead items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_sales_lead_view")
     * @return Response
     */
    public function cgetAction()
    {
        $page = (int) $this->getRequest()->get('page', 1);
        $limit = (int) $this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

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
     * @AclAncestor("orocrm_sales_lead_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Update lead",
     *      resource=true
     * )
     * @AclAncestor("orocrm_sales_lead_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new lead
     *
     * @ApiDoc(
     *      description="Create new lead",
     *      resource=true
     * )
     * @AclAncestor("orocrm_sales_lead_create")
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
     *      description="Delete lead",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_sales_lead_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMSalesBundle:Lead"
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
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('orocrm_sales.lead.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('orocrm_sales.lead.form.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_sales.lead.form.handler.api');
    }
}
