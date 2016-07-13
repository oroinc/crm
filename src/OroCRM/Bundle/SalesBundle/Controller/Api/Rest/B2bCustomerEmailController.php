<?php

namespace OroCRM\Bundle\SalesBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

/**
 * @RouteResource("email")
 * @NamePrefix("oro_api_")
 */
class B2bCustomerEmailController extends RestController implements ClassResourceInterface
{
    /**
     * Create entity B2bCustomerEmail
     * oro_api_post_b2bcustomer_email
     **
     * @return Response
     *
     * @ApiDoc(
     *      description="Create entity",
     *      resource=true,
     *      requirements = {
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     */
    public function postAction()
    {
        $response = $this->handleCreateRequest();

        return $response;
    }

    /**
     * Delete entity B2bCustomerEmail
     * oro_api_delete_b2bcustomer_email
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete B2bCustomerEmail"
     * )
     *
     * @return Response
     */
    public function deleteAction($id)
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
        return $this->get('orocrm_contact.b2bcustomer_email.manager.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_contact.form.type.b2bcustomer_email.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('orocrm_contact.form.type.b2bcustomer_email.type');
    }

    /**
     * {@inheritdoc}
     */
    public function getDeleteHandler()
    {
        return $this->get('orocrm_contact.form.type.b2bcustomer_email.handler');
    }
}
