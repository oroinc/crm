<?php

namespace Oro\Bundle\ContactBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for ContactEmail entity.
 */
class ContactEmailController extends RestController
{
    /**
     * Create entity ContactEmail
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
     * Delete entity ContactEmail
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete ContactEmail"
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
            $errors["errors"] = [$e->getMessage()];
            return new JsonResponse(
                [
                    "code" => $e->getCode(),
                    "message"=>$e->getMessage(),
                    "errors" => $errors
                ],
                $e->getCode()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_contact.contact_email.manager.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_contact.form.type.contact_email.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_contact.form.type.contact_email.type');
    }
}
