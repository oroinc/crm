<?php

namespace OroCRM\Bundle\CampaignBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\CampaignBundle\Entity\Campaign;

/**
 * @Route("/campaign")
 */
class CampaignController extends Controller
{
    /**
     * @Route("/", name="orocrm_campaign_index")
     * @AclAncestor("orocrm_campaign_view")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/create", name="orocrm_campaign_create")
     * @Template("OroCRMCampaignBundle:Campaign:update.html.twig")
     * @Acl(
     *      id="orocrm_campaign_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMCampaignBundle:Campaign"
     * )
     */
    public function createAction()
    {
        return $this->update(new Campaign());
    }

    /**
     * Edit business_unit form
     *
     * @Route("/update/{id}", name="orocrm_campaign_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_campaign_create",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMCampaignBundle:Campaign"
     * )
     */
    public function updateAction(Campaign $entity)
    {
        return $this->update($entity);
    }

    /**
     * @Route("/view/{id}", name="orocrm_campaign_view")
     * @Acl(
     *      id="orocrm_campaign_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMCampaignBundle:Campaign"
     * )
     * @Template
     */
    public function viewAction()
    {
        return [];
    }

    /**
     * @param Campaign $entity
     * @return array
     */
    protected function update(Campaign $entity)
    {
        if ($this->get('orocrm_campaign.campaign.form.handler')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.business_unit.controller.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'orocrm_campaign_update', 'parameters' => ['id' => $entity->getId()]],
                ['route' => 'orocrm_campaign_view', 'parameters' => ['id' => $entity->getId()]],
                $entity
            );
        }

        return [
            'entity' => $entity,
            'form' => $this->get('orocrm_campaign.campaign.form')->createView()
        ];
    }
}
