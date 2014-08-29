<?php

namespace OroCRM\Bundle\SalesBundle\Controller\Api\Rest;

use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class B2bCustomerController extends RestController implements ClassResourceInterface
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete B2bCustomer",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_b2bcustomer_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMSalesBundle:B2bCustomer"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \BadMethodCallException('Form is not available.');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('orocrm_sales.b2bcustomer.manager.api');
    }
}
