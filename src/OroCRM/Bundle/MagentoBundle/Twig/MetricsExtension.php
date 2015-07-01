<?php

namespace OroCRM\Bundle\MagentoBundle\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

class MetricsExtension extends Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('orocrm_magento_filter_metric_items', [$this, 'filterItems']),
        ];
    }

    /**
     * @param array $items
     * @param array|null $config Json encoded configuration
     *
     * @return array
     */
    public function filterItems(array $items, array $config = null)
    {
        if (!isset($config['items'])) {
            return $items;
        }

        $configItems = $this->getSortedConfigItems($config);

        return $this->sortItemsByConfigItems($items, $configItems);
    }

    /**
     * @param array $configuration
     *
     * @return array
     */
    protected function getSortedConfigItems(array $configuration)
    {
        $items = [];
        foreach ($configuration['items'] as $item) {
            if (!$item['show']) {
                continue;
            }

            $items[$item['id']] = $item;
        }
        uasort($items, function ($a, $b) {
            return $a['order'] - $b['order'];
        });
        
        return $items;
    }

    /**
     * @param array $items
     * @param array $sortedConfigItems
     *
     * @return array
     */
    protected function sortItemsByConfigItems(array $items, array $sortedConfigItems)
    {
        $result = array_intersect_key($items, $sortedConfigItems);

        $sortedKeys = array_flip(array_keys($sortedConfigItems));
        uksort($result, function ($a, $b) use ($sortedKeys) {
            return $sortedKeys[$a] - $sortedKeys[$b];
        });

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_magento.metrics';
    }
}
