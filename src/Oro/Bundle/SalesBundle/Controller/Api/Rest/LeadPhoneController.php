<?php

namespace Oro\Bundle\SalesBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for LeadPhone entity.
 */
class LeadPhoneController extends RestController
{
    /**
     * Create entity LeadPhone
     *
     * @return Response
     *
     * @ApiDoc(
     *      description="Create entity Lead phone",
     *      resource=true
     * )
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Delete entity LeadPhone
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete LeadPhone"
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
            return new JsonResponse(["code" => $e->getCode(), "message"=>$e->getMessage() ], $e->getCode());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_sales.lead_phone.manager.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_sales.form.type.lead_phone.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_sales.form.type.lead_phone.type');
    }
}
