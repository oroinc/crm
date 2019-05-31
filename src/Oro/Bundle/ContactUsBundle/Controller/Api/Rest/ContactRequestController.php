<?php

namespace Oro\Bundle\ContactUsBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\ContactUsBundle\Form\Handler\ContactRequestHandler;
use Oro\Bundle\ContactUsBundle\Form\Type\ContactRequestType;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides get API action to find the Customer entity.
 *
 * @RouteResource("contactrequest")
 * @NamePrefix("oro_api_")
 */
class ContactRequestController extends RestController implements ClassResourceInterface, ServiceSubscriberInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get contact request item",
     *      resource=true
     * )
     * @AclAncestor("oro_contactus_request_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_contact_us.contact_request.manager.api');
    }

    /**
     * @return ContactRequestHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_contact_us.contact_request.form.handler');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_contact_us.embedded_form');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_contact_us.contact_request.manager.api' => ApiEntityManager::class,
            'oro_contact_us.contact_request.form.handler' => ContactRequestHandler::class,
            'oro_contact_us.embedded_form' => ContactRequestType::class,
        ];
    }
}
