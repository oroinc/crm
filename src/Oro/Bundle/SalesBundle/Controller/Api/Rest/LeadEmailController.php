<?php

namespace Oro\Bundle\SalesBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for LeadEmail entity.
 */
class LeadEmailController extends RestController
{
    /**
     * Create entity LeadEmail
     *
     * @return Response
     *
     * @ApiDoc(
     *      description="Create entity Lead email",
     *      resource=true
     * )
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Delete entity LeadEmail
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete LeadEmail"
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
        return $this->get('oro_sales.lead_email.manager.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_sales.form.type.lead_email.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_sales.form.type.lead_email.type');
    }
}
