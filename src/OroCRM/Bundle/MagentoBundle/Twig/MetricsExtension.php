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
        if (!isset($config['metrics'])) {
            return $items;
        }

        $metrics = $this->getSortedMetrics($config);

        return $this->sortItemsByMetrics($items, $metrics);
    }

    /**
     * @param array $configuration
     *
     * @return array
     */
    protected function getSortedMetrics(array $configuration)
    {
        $metrics = [];
        foreach ($configuration['metrics'] as $metric) {
            if (!$metric['show']) {
                continue;
            }

            $metrics[$metric['id']] = $metric;
        }
        uasort($metrics, function ($a, $b) {
            return $a['order'] - $b['order'];
        });
        
        return $metrics;
    }

    /**
     * @param array $items
     * @param array $sortedMetrics
     *
     * @return array
     */
    protected function sortItemsByMetrics(array $items, array $sortedMetrics)
    {
        $result = array_intersect_key($items, $sortedMetrics);

        $sortedKeys = array_flip(array_keys($sortedMetrics));
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
