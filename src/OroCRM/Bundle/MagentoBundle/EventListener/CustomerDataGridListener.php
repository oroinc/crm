<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension;

class CustomerDataGridListener
{
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
                'label' => 'orocrm.magento.datagrid.columns.is_subscriber.label',
                'type' => 'single_choice',
                'data_name' => 'isSubscriber',
                'options' => [
                    'field_options' => [
                        'choices' => [
                            'unknown' => 'orocrm.magento.datagrid.columns.is_subscriber.unknown',
                            'no' => 'orocrm.magento.datagrid.columns.is_subscriber.no',
                            'yes' => 'orocrm.magento.datagrid.columns.is_subscriber.yes'
                        ]
                    ]
                ]
            ]
        );

        $filters = $parameters->get(OrmFilterExtension::FILTER_ROOT_PARAM, []);
        if (empty($filters['isSubscriber'])) {
            return;
        }

        $query = $config->offsetGetByPath('[source][query]', []);
        foreach ($query['select'] as &$field) {
            if ($field === 'c.id') {
                $field = 'DISTINCT ' . $field;
                break;
            }
        }
        $query['select'][] = 'CASE WHEN'
            . ' transport.isExtensionInstalled = true AND transport.extensionVersion IS NOT NULL'
            . ' THEN (CASE WHEN IDENTITY(newsletterSubscribers.status) = \'1\' THEN \'yes\' ELSE \'no\' END)'
            . ' ELSE \'unknown\''
            . ' END as isSubscriber';

        $query['join']['left'][] = [
            'join' => 'c.channel',
            'alias' => 'channel'
        ];
        $query['join']['left'][] = [
            'join' => 'OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport',
            'alias' => 'transport',
            'conditionType' => 'WITH',
            'condition' => 'channel.transport = transport'
        ];
        $query['join']['left'][] = [
            'join' => 'c.newsletterSubscribers',
            'alias' => 'newsletterSubscribers'
        ];
        $config->offsetSetByPath('[source][query]', $query);
    }
}
