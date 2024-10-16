<?php

namespace Oro\Bundle\ContactUsBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\ContactUsBundle\Form\Handler\ContactRequestHandler;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API controller to get the ContactRequest entity.
 */
class ContactRequestController extends RestController
{
    /**
     * REST GET item
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Get contact request item",
     *      resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_contactus_request_view')]
    public function getAction(int $id)
    {
        return $this->handleGetRequest($id);
    }

    #[\Override]
    public function getManager()
    {
        return $this->container->get('oro_contact_us.contact_request.manager.api');
    }

    /**
     * @return ContactRequestHandler
     */
    #[\Override]
    public function getFormHandler()
    {
        return $this->container->get('oro_contact_us.contact_request.form.handler');
    }

    #[\Override]
    public function getForm()
    {
        return $this->container->get('oro_contact_us.embedded_form');
    }
}
