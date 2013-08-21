<?php

namespace OroCRM\Bundle\SalesBundle\Controller\Api\Rest;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

/**
 * @RouteResource("opportunity")
 * @NamePrefix("oro_api_")
 */
class OpportunityController extends RestController implements ClassResourceInterface
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete opportunity",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_opportunity_delete",
     *      name="Delete opportunity",
     *      description="Delete opportunity",
     *      parent="orocrm_opportunity"
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
        return $this->get('orocrm_sales.opportunity.manager.api');
    }

    /**
     * @return FormInterface
     * @throws \LogicException
     */
    public function getForm()
    {
        throw new \LogicException('Need to create API form for opportunity');
    }

    /**
     * @return ApiFormHandler
     * @throws \LogicException
     */
    public function getFormHandler()
    {
        throw new \LogicException('Need to create API form handler for opportunity');
    }
}
