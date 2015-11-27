<?php

namespace OroCRM\Bundle\ContactBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\FormBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

/**
 * @RouteResource("email")
 * @NamePrefix("oro_api_")
 */
class ContactEmailController extends RestController implements ClassResourceInterface
{
    /**
     * Create entity ContactEmail
     * oro_api_post_contact_email
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
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_contact.contact_email.manager.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_contact.form.type.contact_email.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('orocrm_contact.form.type.contact_email.type');
    }
}
