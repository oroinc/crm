<?php
namespace OroCRM\Bundle\MarketingListBundle\EventListener;

use Oro\Bundle\EntityExtendBundle\Event\ValueRenderEvent;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class ExtendFieldValueBeforeRenderListener
{
    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager) {
        $this->entityProvider = $configManager->getProvider('entity');
    }

    /**
     * {@inheritdoc}
     */
    public function beforeValueRender(ValueRenderEvent $event)
    {
        $fieldConfig = $this->entityProvider->getConfigById($event->getFieldConfigId());
        $contactInformationType = $fieldConfig->get('contact_information');

        // if some contact information type is defined -- applies proper template for its value
        if ($contactInformationType) {
            $template = "OroCRMMarketingListBundle:MarketingList/ExtendField:{$contactInformationType}.html.twig";
            $event->setFieldViewValue([
                'value' => $event->getFieldValue(),
                'entity' => $event->getEntity(),
                'template' => $template,
            ]);
        }
    }
}
