<?php

namespace Oro\Bundle\MagentoBundle\Provider\Connector;

class RegionConnector extends AbstractMagentoConnector implements DictionaryConnectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.magento.connector.region.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return self::REGION_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName()
    {
        return 'mage_region_import';
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'region_dictionary';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConnectorSource()
    {
        if (!empty($this->bundleConfiguration['sync_settings']['region_sync_interval'])) {
            $interval = \DateInterval::createFromDateString(
                $this->bundleConfiguration['sync_settings']['region_sync_interval']
            );

            $dateToCheck = new \DateTime('now', new \DateTimeZone('UTC'));
            $dateToCheck->sub($interval);

            $lastStatus = $this->getLastCompletedIntegrationStatus($this->channel, $this->getType());

            if ($lastStatus && $lastStatus->getDate() > $dateToCheck) {
                $this->logger->info(
                    sprintf(
                        'Regions are up to date, last sync date is %s, interval is %s',
                        $lastStatus->getDate()->format(\DateTime::RSS),
                        $this->bundleConfiguration['sync_settings']['region_sync_interval']
                    )
                );

                return new \EmptyIterator();
            }
        }

        return $this->transport->getRegions();
    }
}
