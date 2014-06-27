<?php

namespace OroCRM\Bundle\ChannelBundle\Controller\Api\Rest;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

/**
 * @RouteResource("channel")
 * @NamePrefix("orocrm_api_")

 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class ChannelController extends RestController
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete Channel",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_channel_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMChannelBundle:Channel"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('orocrm_channel.form.channel');
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('orocrm_channel.channel.manager');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
    }
}
