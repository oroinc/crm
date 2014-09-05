<?php

namespace OroCRM\Bundle\CampaignBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

/**
 * @RouteResource("emailcampaign")
 * @NamePrefix("oro_api_")
 */
class EmailCampaignController extends RestController implements ClassResourceInterface
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete Email Campaign",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_email_campaign_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMCampaignBundle:EmailCampaign"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_campaign.email_campaign.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \BadMethodCallException('Form is not available.');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }
}
