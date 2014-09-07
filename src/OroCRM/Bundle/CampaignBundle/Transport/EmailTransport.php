<?php

namespace OroCRM\Bundle\CampaignBundle\Transport;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

class EmailTransport
{
    /**
     * @var MarketingListProvider
     */
    protected $marketingListProvider;

    /**
     * @var ContactInformationFieldHelper
     */
    protected $contactInformationFieldHelper;

    /**
     * @var Processor $processor
     */
    protected $processor;

    /**
     * @var EmailRenderer
     */
    protected $emailRenderer;

    /**
     * @param MarketingListProvider         $marketingListProvider
     * @param ContactInformationFieldHelper $contactInformationFieldHelper
     * @param Processor                     $processor
     * @param EmailRenderer                 $emailRenderer
     */
    public function __construct(
        MarketingListProvider $marketingListProvider,
        ContactInformationFieldHelper $contactInformationFieldHelper,
        Processor $processor,
        EmailRenderer $emailRenderer
    ) {
        $this->marketingListProvider         = $marketingListProvider;
        $this->contactInformationFieldHelper = $contactInformationFieldHelper;
        $this->processor                     = $processor;
        $this->emailRenderer                 = $emailRenderer;
    }

    /**
     * @param EmailCampaign $campaign
     */
    public function send(EmailCampaign $campaign)
    {
        $marketingList = $campaign->getMarketingList();

        foreach ($this->getIterator($marketingList) as $entity) {
            list ($subjectRendered, $templateRendered) = $this->emailRenderer->compileMessage(
                $campaign->getTemplate(),
                ['entity' => $entity]
            );

            $emailModel = new Email();

            /** @todo: from system configuration */
            $emailModel->setFrom('mail@example.com');

            $contactInformationFields = $this
                ->contactInformationFieldHelper
                ->getEntityContactInformationColumns($marketingList->getEntity());

            $emailFields = array_filter(
                $contactInformationFields,
                function ($contactInformationField) {
                    return $contactInformationField === 'email';
                }
            );

            $propertyAccessor = PropertyAccess::createPropertyAccessor();

            $emails = array_map(
                function ($emailField) use ($propertyAccessor, $entity) {
                    return $propertyAccessor->getValue($entity, $emailField);
                },
                array_keys($emailFields)
            );

            $emailModel->setTo($emails);
            $emailModel->setSubject($subjectRendered);
            $emailModel->setBody($templateRendered);

            $this->processor->process($emailModel);
        }
    }

    /**
     * @param MarketingList $marketingList
     *
     * @return \Iterator
     */
    protected function getIterator(MarketingList $marketingList)
    {
        /** @todo entities iterator */
        return $this->marketingListProvider->getMarketingListResultIterator($marketingList);
    }
}
