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
     * @param string $configValue Json encoded configuration
     *
     * @return array
     */
    public function filterItems(array $items, $configValue)
    {
        $config = json_decode($configValue, true);
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
        $keyOrder = array_flip(array_keys($sortedMetrics));
        uksort($items, function ($a, $b) use ($keyOrder) {
            if (!isset($keyOrder[$a], $keyOrder[$b])) {
                return 0;
            }

            return $keyOrder[$a] - $keyOrder[$b];
        });

        return array_intersect_key($items, $keyOrder);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_magento.metrics';
    }
}
