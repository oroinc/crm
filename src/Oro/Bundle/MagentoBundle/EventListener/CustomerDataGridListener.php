<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\DataGridBundle\Provider\SelectedFields\SelectedFieldsProviderInterface;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;

/**
 * Adds "isSubscriber" filter to datagrid.
 *
 * Adds joins to query config only if the filter is active.
 */
class CustomerDataGridListener
{
    /** @var SelectedFieldsProviderInterface */
    private $selectedFieldsFromFiltersProvider;

    /**
     * @param SelectedFieldsProviderInterface $selectedFieldsFromFiltersProvider
     */
    public function __construct(SelectedFieldsProviderInterface $selectedFieldsFromFiltersProvider)
    {
        $this->selectedFieldsFromFiltersProvider = $selectedFieldsFromFiltersProvider;
    }

    /**
     * @param PreBuild $event
     */
    public function onPreBuild(PreBuild $event)
    {
        $config = $event->getConfig();
        $parameters = $event->getParameters();
        $this->addNewsletterSubscribers($config, $parameters);
    }

    /**
     * @param DatagridConfiguration $config
     * @param ParameterBag          $parameters
     */
    protected function addNewsletterSubscribers(DatagridConfiguration $config, ParameterBag $parameters)
    {
        $config->addFilter(
            'isSubscriber',
            [
                'label'     => 'oro.magento.datagrid.columns.is_subscriber.label',
                'type'      => 'single_choice',
                'data_name' => 'isSubscriber',
                'options'   => [
                    'field_options' => [
                        'choices' => [
                            'oro.magento.datagrid.columns.is_subscriber.unknown' => 'unknown',
                            'oro.magento.datagrid.columns.is_subscriber.no' => 'no',
                            'oro.magento.datagrid.columns.is_subscriber.yes' => 'yes',
                        ]
                    ]
                ]
            ]
        );

        if ($this->isSubscriberFilterActive($config, $parameters)) {
            $query = $config->getOrmQuery();
            $query->setDistinct();
            $query->addSelect(
                sprintf(
                    'CASE WHEN transport.isExtensionInstalled = true AND transport.extensionVersion IS NOT NULL'
                    . ' THEN (CASE WHEN IDENTITY(newsletterSubscribers.status) = \'%s\' THEN \'yes\' ELSE \'no\' END)'
                    . ' ELSE \'unknown\''
                    . ' END as isSubscriber',
                    NewsletterSubscriber::STATUS_SUBSCRIBED
                )
            );
            $query->addLeftJoin('c.channel', 'channel');
            $query->addLeftJoin(
                'Oro\Bundle\MagentoBundle\Entity\MagentoTransport',
                'transport',
                'WITH',
                'channel.transport = transport'
            );
            $query->addLeftJoin('c.newsletterSubscribers', 'newsletterSubscribers');
        }
    }

    /**
     * @param DatagridConfiguration $config
     * @param ParameterBag $parameters
     *
     * @return bool
     */
    private function isSubscriberFilterActive(DatagridConfiguration $config, ParameterBag $parameters): bool
    {
        $selectedFields = $this->selectedFieldsFromFiltersProvider->getSelectedFields($config, $parameters);

        return \in_array('isSubscriber', $selectedFields, false);
    }
}
