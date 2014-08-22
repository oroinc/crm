<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\SegmentBundle\Grid\SegmentDatagridConfigurationBuilder;

class MarketingListItemDatagridConfigurationBuilder extends SegmentDatagridConfigurationBuilder
{
    const MARKETING_LIST = 'OroCRM\Bundle\MarketingListBundle\Entity\MarketingList';

    const GRID_NAME = 'orocrm-marketing-list-items-grid';

    /**
     * @var ConfigurationProviderInterface
     */
    protected $configurationProvider;

    /**
     * @param ConfigurationProviderInterface $configurationProvider
     */
    public function setConfigurationProvider(ConfigurationProviderInterface $configurationProvider)
    {
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configuration     = parent::getConfiguration();
        $gridConfiguration = $this->configurationProvider->getConfiguration(self::GRID_NAME);
        $scopes            = array_diff(array_keys($configuration->getIterator()->getArrayCopy()), ['name']);

        foreach ($scopes as $scope) {
            $path             = sprintf('[%s]', $scope);
            $additionalParams = $gridConfiguration->offsetGetByPath($path);

            if (empty($additionalParams)) {
                continue;
            }

            $baseParams = $configuration->offsetGetByPath($path);
            $configuration->offsetSetByPath($path, array_merge_recursive($baseParams, $additionalParams));
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($gridName)
    {
        if (!parent::isApplicable($gridName)) {
            return false;
        }

        $segmentId = str_replace(Segment::GRID_PREFIX, '', $gridName);
        if (empty($segmentId)) {
            return false;
        }

        $entity = $this->doctrine
            ->getManagerForClass(self::MARKETING_LIST)
            ->getRepository(self::MARKETING_LIST)
            ->findOneBy(['segment' => $segmentId]);

        return (bool)$entity;
    }
}
