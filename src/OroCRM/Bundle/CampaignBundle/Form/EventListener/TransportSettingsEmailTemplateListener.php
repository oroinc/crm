<?php

namespace OroCRM\Bundle\CampaignBundle\Form\EventListener;

use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\EmailBundle\Entity\Repository\EmailTemplateRepository;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class TransportSettingsEmailTemplateListener implements EventSubscriberInterface
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @param RegistryInterface        $registry
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(RegistryInterface $registry, SecurityContextInterface $securityContext)
    {
        $this->registry        = $registry;
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSet',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        ];
    }

    /**
     * Fill template choices based on Existing EmailCampaign{MarketingList} entity class.
     *
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $entityName = $event->getForm()->getParent()->getData()->getEntityName();
        $this->fillEmailTemplateChoices($event->getForm(), $entityName);
    }

    /**
     * Fill template choices based on new EmailCampaign{MarketingList} entity class
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (empty($data['parentData']['marketingList'])) {
            return;
        }

        $marketingList = $this->getMarketingListById((int)$data['parentData']['marketingList']);
        if (is_null($marketingList)) {
            return;
        }

        $entityName = $marketingList->getEntity();
        $this->fillEmailTemplateChoices($event->getForm(), $entityName);
    }

    /**
     * @param int $id
     *
     * @return MarketingList
     */
    protected function getMarketingListById($id)
    {
        return $this->registry
            ->getRepository('OroCRMMarketingListBundle:MarketingList')
            ->find($id);
    }

    /**
     * @param FormInterface $form
     * @param string        $entityName
     */
    protected function fillEmailTemplateChoices(FormInterface $form, $entityName)
    {
        /** @var UsernamePasswordOrganizationToken $token */
        $token = $this->securityContext->getToken();

        FormUtils::replaceField(
            $form,
            'template',
            [
                'selectedEntity' => $entityName,
                'query_builder'  => function (EmailTemplateRepository $templateRepository) use ($entityName, $token) {
                    return $templateRepository->getEntityTemplatesQueryBuilder(
                        $entityName,
                        $token->getOrganizationContext(),
                        true
                    );
                },
            ],
            ['choice_list', 'choices']
        );
    }
}
