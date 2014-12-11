<?php

namespace OroCRM\Bundle\CampaignBundle\Controller\Api\Rest;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Response;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
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

        $marketingList = $this
            ->getDoctrine()
            ->getRepository('OroCRMMarketingListBundle:MarketingList')
            ->find((int)$id);

        if (!$marketingList) {
            return $this->handleView(
                $this->view(null, Codes::HTTP_NOT_FOUND)
            );
        }

        $securityContext = $this->get('security.context');
        /** @var UsernamePasswordOrganizationToken $token */
        $token        = $securityContext->getToken();
        $organization = $token->getOrganizationContext();

        $templatesQueryBuilder = $this
            ->getDoctrine()
            ->getRepository('OroEmailBundle:EmailTemplate')
            ->getEntityTemplatesQueryBuilder($marketingList->getEntity(), $organization, true);

        $templates = $templatesQueryBuilder->getQuery()->getArrayResult();
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
