<?php

namespace Oro\Bundle\SalesBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * API CRUD controller for LeadPhone entity.
 *
 * @RouteResource("phone")
 * @NamePrefix("oro_api_")
 */
class LeadPhoneController extends RestController implements ClassResourceInterface
{
    /**
     * Create entity LeadPhone
     * oro_api_post_lead_phone
     *
     * @return Response
     *
     * @ApiDoc(
     *      description="Create entity Lead phone",
     *      resource=true,
     *      requirements = {
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Delete entity LeadPhone
     * oro_api_delete_lead_phone
     *
     * @param int $id
     *
     * @Rest\Delete(requirements={"id"="\d+"})
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
