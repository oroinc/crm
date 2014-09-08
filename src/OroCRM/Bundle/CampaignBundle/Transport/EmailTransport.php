<?php

namespace OroCRM\Bundle\CampaignBundle\Transport;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Security\Core\Util\ClassUtils;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Mailer\Processor;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListItemConnector;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

class EmailTransport
{
    const CONTACT_INFORMATION_SCOPE = 'email';

    /**
     * @var MarketingListProvider
     */
    protected $marketingListProvider;

    /**
     * @var ContactInformationFieldHelper
     */
    protected $contactInformationFieldHelper;

    /**
     * @var MarketingListItemConnector
     */
    protected $marketingListItemConnector;

    /**
     * @var Processor $processor
     */
    protected $processor;

    /**
     * @var EmailRenderer
     */
    protected $emailRenderer;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param MarketingListProvider         $marketingListProvider
     * @param ContactInformationFieldHelper $contactInformationFieldHelper
     * @param MarketingListItemConnector    $marketingListItemConnector
     * @param Processor                     $processor
     * @param EmailRenderer                 $emailRenderer
     * @param ConfigManager                 $configManager
     */
    public function __construct(
        MarketingListProvider $marketingListProvider,
        ContactInformationFieldHelper $contactInformationFieldHelper,
        MarketingListItemConnector $marketingListItemConnector,
        Processor $processor,
        EmailRenderer $emailRenderer,
        ConfigManager $configManager
    ) {
        $this->marketingListProvider         = $marketingListProvider;
        $this->contactInformationFieldHelper = $contactInformationFieldHelper;
        $this->marketingListItemConnector    = $marketingListItemConnector;
        $this->processor                     = $processor;
        $this->emailRenderer                 = $emailRenderer;
        $this->configManager                 = $configManager;
    }

    /**
     * @param EmailCampaign $campaign
     */
    public function send(EmailCampaign $campaign)
    {
        $marketingList = $campaign->getMarketingList();

        foreach ($this->getIterator($marketingList) as $entity) {
            $entityId = $entity->getId();

            list ($subjectRendered, $templateRendered) = $this->emailRenderer->compileMessage(
                $campaign->getTemplate(),
                ['entity' => $entity]
            );

            $to = $this->getEmailAddresses($marketingList->getSegment(), $entity);

            $emailModel = new Email();
            $emailModel
                ->setFrom($this->getFromEmail($campaign))
                ->setEntityClass($marketingList->getEntity())
                ->setEntityId($entityId)
                ->setTo($to)
                ->setSubject($subjectRendered)
                ->setBody($templateRendered);

            $this->processor->process($emailModel);

            $this->marketingListItemConnector->contact($marketingList, $entityId);
        }
    }

    /**
     * @param EmailCampaign $campaign
     *
     * @return string
     */
    protected function getFromEmail(EmailCampaign $campaign)
    {
        if ($fromEmail = $campaign->getFromEmail()) {
            return $fromEmail;
        }

        return $this->configManager->get('oro_crm_campaign.campaign_from_email');
    }

    /**
     * @param AbstractQueryDesigner $abstractQueryDesigner
     * @param object                $entity
     *
     * @return string[]
     */
    protected function getEmailAddresses(AbstractQueryDesigner $abstractQueryDesigner, $entity)
    {
        $definitionColumns = [];

        $definition = $abstractQueryDesigner->getDefinition();
        if ($definition) {
            $definition = json_decode($definition, JSON_OBJECT_AS_ARRAY);
            if (!empty($definition['columns'])) {
                $definitionColumns = array_map(
                    function (array $columnDefinition) {
                        return $columnDefinition['name'];
                    },
                    $definition['columns']
                );
            }
        }

        $contactInformationFields = $this
            ->contactInformationFieldHelper
            ->getEntityContactInformationColumns(ClassUtils::getRealClass($entity));

        $emailFields = array_filter(
            $contactInformationFields,
            function ($contactInformationField) {
                return $contactInformationField === self::CONTACT_INFORMATION_SCOPE;
            }
        );

        if (!empty($definitionColumns)) {
            $emailFields = array_intersect(array_keys($emailFields), $definitionColumns);
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        return array_map(
            function ($emailField) use ($propertyAccessor, $entity) {
                return (string)$propertyAccessor->getValue($entity, $emailField);
            },
            $emailFields
        );
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
