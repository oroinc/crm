<?php

namespace OroCRM\Bundle\CampaignBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Form\Handler\EmailCampaignHandler;

/**
 * @Route("/campaign/email")
 */
class EmailCampaignController extends Controller
{
    /**
     * @Route("/", name="orocrm_email_campaign_index")
     * @AclAncestor("orocrm_email_campaign_view")
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orocrm_campaign.email_campaign.entity.class')
        ];
    }

    /**
     * Create email campaign
     *
     * @Route("/create", name="orocrm_email_campaign_create")
     * @Template("OroCRMCampaignBundle:EmailCampaign:update.html.twig")
     * @Acl(
     *      id="orocrm_email_campaign_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMCampaignBundle:EmailCampaign"
     * )
     */
    public function createAction()
    {
        return $this->update(new EmailCampaign());
    }

    /**
     * Edit email campaign
     *
     * @Route("/update/{id}", name="orocrm_email_campaign_update", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     * @Acl(
     *      id="orocrm_email_campaign_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMCampaignBundle:EmailCampaign"
     * )
     * @param EmailCampaign $entity
     * @return array
     */
    public function updateAction(EmailCampaign $entity)
    {
        return $this->update($entity);
    }

    /**
     * View email campaign
     *
     * @Route("/view/{id}", name="orocrm_email_campaign_view", requirements={"id"="\d+"})
     * @Acl(
     *      id="orocrm_email_campaign_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMCampaignBundle:EmailCampaign"
     * )
     * @Template
     * @param EmailCampaign $entity
     * @return array
     */
    public function viewAction(EmailCampaign $entity)
    {
        $stats = $this->getDoctrine()
            ->getRepository("OroCRMCampaignBundle:EmailCampaignStatistics")
            ->getEmailCampaignStats($entity);

        return [
            'entity' => $entity,
            'stats' => $stats,
            'show_stats' => (bool) array_sum($stats),
            'send_allowed' => $this->isManualSendAllowed($entity)
        ];
    }

    /**
     * Process save email campaign entity
     *
     * @param EmailCampaign $entity
     * @return array
     */
    protected function update(EmailCampaign $entity)
    {
        if ($this->get('orocrm_campaign.form.handler.email_campaign')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.campaign.emailcampaign.controller.saved.message')
            );

            return $this->get('oro_ui.router')->redirect($entity);
        }
        $form = $this->getForm();

        return [
            'entity' => $entity,
            'form'   => $form->createView()
        ];
    }

    /**
     * Returns form instance
     *
     * @return FormInterface
     */
    protected function getForm()
    {
        $isUpdateOnly = $this->get('request')->get(EmailCampaignHandler::UPDATE_MARKER, false);

        $form = $this->get('orocrm_campaign.email_campaign.form');
        if ($isUpdateOnly) {
            // substitute submitted form with new not submitted instance to ignore validation errors
            // on form after transport field was changed
            $form = $this->get('form.factory')
                ->createNamed('orocrm_email_campaign', 'orocrm_email_campaign', $form->getData());
        }

        return $form;
    }

    /**
     * @Route("/send/{id}", name="orocrm_email_campaign_send", requirements={"id"="\d+"})
     * @Acl(
     *      id="orocrm_email_campaign_send",
     *      type="action",
     *      label="orocrm.campaign.acl.send_emails.label",
     *      description="orocrm.campaign.acl.send_emails.description",
     *      group_name=""
     * )
     *
     * @param EmailCampaign $entity
     * @return array
     */
    public function sendAction(EmailCampaign $entity)
    {
        if ($this->isManualSendAllowed($entity)) {
            $senderFactory = $this->get('orocrm_campaign.email_campaign.sender.builder');
            $sender = $senderFactory->getSender($entity);
            $sender->send($entity);

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.campaign.emailcampaign.controller.sent')
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                'error',
                $this->get('translator')->trans('orocrm.campaign.emailcampaign.controller.send_disallowed')
            );
        }

        return $this->redirect(
            $this->generateUrl(
                'orocrm_email_campaign_view',
                ['id' => $entity->getId()]
            )
        );
    }

    /**
     * @param EmailCampaign $entity
     * @return bool
     */
    protected function isManualSendAllowed(EmailCampaign $entity)
    {
        $sendAllowed = $entity->getSchedule() === EmailCampaign::SCHEDULE_MANUAL
            && !$entity->isSent()
            && $this->get('oro_security.security_facade')->isGranted('orocrm_email_campaign_send');

        if ($sendAllowed) {
            $transportSettings = $entity->getTransportSettings();
            if ($transportSettings) {
                $validator = $this->get('validator');
                $errors = $validator->validate($transportSettings);
                $sendAllowed = count($errors) === 0;
            }
        }

        return $sendAllowed;
    }
}
