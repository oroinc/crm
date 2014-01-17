<?php

namespace OroCRM\Bundle\SalesBundle\Controller\Api\Rest;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

/**
 * @RouteResource("sales_flow_opportunity")
 * @NamePrefix("oro_api_")
 */
class SalesFlowOpportunityController extends RestController implements ClassResourceInterface
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete sales flow opportunity",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_sales_sales_flow_opportunity_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMSalesBundle:SalesFlowOpportunity"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_sales.sales_flow_opportunity.manager.api');
    }

    /**
     * {@inheritDoc}
     */
    public function getForm()
    {
        throw new \LogicException('This method should not be called');
    }

    /**
     * {@inheritDoc}
     */
    public function getFormHandler()
    {
        throw new \LogicException('This method should not be called');
    }
}
