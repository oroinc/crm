<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

class NewsletterSubscriberInitialSyncProcessor extends AbstractInitialProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process(Integration $integration, $connector = null, array $parameters = [])
    {
        $parameters['initial_id'] = $this->getSyncedToId($integration);

        return parent::process($integration, $connector, $parameters);
    }

    /**
     * @param Integration $integration
     *
     * @return int|null
     */
    protected function getSyncedToId(Integration $integration)
    {
        return $integration->getTransport()->getSettingsBag()->get('newsletter_subscriber_synced_to_id');
    }
}
