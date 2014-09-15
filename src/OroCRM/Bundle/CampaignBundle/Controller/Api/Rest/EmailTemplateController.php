<?php

namespace OroCRM\Bundle\CampaignBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

/**
 * @RouteResource("emailcampaign_email_template")
 * @NamePrefix("orocrm_api_")
 */
class EmailTemplateController extends RestController
{
    /**
     * REST GET email campaign templates by entity name
     *
     * @param string $id
     *
     * @ApiDoc(
     *     description="Get email campaign templates by entity name",
     *     resource=true
     * )
     * @AclAncestor("oro_email_emailtemplate_index")
     *
     * @return Response
     */
    public function cgetAction($id = null)
    {
        if (!$id) {
            return $this->handleView(
                $this->view(null, Codes::HTTP_NOT_FOUND)
            );
        }

        $entity = $this
            ->getDoctrine()
            ->getRepository('OroCRMMarketingListBundle:MarketingList')
            ->find((int)$id);

        if (!$entity) {
            return $this->handleView(
                $this->view(null, Codes::HTTP_NOT_FOUND)
            );
        }

        $templates = $this
            ->getDoctrine()
            ->getRepository('OroEmailBundle:EmailTemplate')
            ->getTemplateByEntityName($entity->getEntity());

        return $this->handleView(
            $this->view($templates, Codes::HTTP_OK)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('Not implemented');
    }
}
