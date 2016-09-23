<?php

namespace Oro\Bundle\CampaignBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\CampaignBundle\Entity\Campaign;

/**
 * @Route("/campaign")
 */
class CampaignController extends Controller
{
    /**
     * @Route("/", name="oro_campaign_index")
     * @AclAncestor("oro_campaign_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('oro_campaign.entity.class')
        ];
    }

    /**
     * Create campaign
     *
     * @Route("/create", name="oro_campaign_create")
     * @Template("OroCampaignBundle:Campaign:update.html.twig")
     * @Acl(
     *      id="oro_campaign_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCampaignBundle:Campaign"
     * )
     */
    public function createAction()
    {
        return $this->update(new Campaign());
    }

    /**
     * Edit campaign
     *
     * @Route("/update/{id}", name="oro_campaign_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="oro_campaign_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCampaignBundle:Campaign"
     * )
     */
    public function updateAction(Campaign $entity)
    {
        return $this->update($entity);
    }

    /**
     * View campaign
     *
     * @Route("/view/{id}", name="oro_campaign_view")
     * @Acl(
     *      id="oro_campaign_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCampaignBundle:Campaign"
     * )
     * @Template
     */
    public function viewAction(Campaign $entity)
    {
        return ['entity' => $entity];
    }

    /**
     * Process save campaign entity
     *
     * @param Campaign $entity
     * @return array
     */
    protected function update(Campaign $entity)
    {
        return $this->get('oro_form.model.update_handler')->update(
            $entity,
            $this->get('oro_campaign.campaign.form'),
            $this->get('translator')->trans('oro.campaign.controller.campaign.saved.message')
        );
    }
}
