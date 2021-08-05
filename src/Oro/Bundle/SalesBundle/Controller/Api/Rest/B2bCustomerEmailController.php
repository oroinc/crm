<?php

namespace Oro\Bundle\SalesBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for B2bCustomerEmail entity.
 */
class B2bCustomerEmailController extends RestController
{
    /**
     * Create entity B2bCustomerEmail
     *
     * @return Response
     *
     * @ApiDoc(
     *      description="Create entity",
     *      resource=true
     * )
     */
    public function postAction()
    {
        $response = $this->handleCreateRequest();

        return $response;
    }

    /**
     * Delete entity B2bCustomerEmail
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete B2bCustomerEmail"
     * )
     *
     * @return Response
     */
    public function deleteAction(int $id)
    {
        try {
            $this->getDeleteHandler()->handleDelete($id, $this->getManager());

            return new JsonResponse(["id" => ""]);
        } catch (\Exception $e) {
            return new JsonResponse(["code" => $e->getCode(), "message" => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_sales.b2bcustomer_email.manager.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_sales.form.type.b2bcustomer_email.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_sales.form.type.b2bcustomer_email.type');
    }
}
