<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Event\PreBuild;

class CustomerDataGridListener
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param PreBuild $event
     */
    public function onPreBuild(PreBuild $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $config = $event->getConfig();
        $this->addSubscribersFilter($config);

        $requestParameters = $request->query->get('magento-customers-grid', false);
        if ($requestParameters && !empty($requestParameters['_filter']['isSubscriber'])) {
            $this->addNewsletterSubscribers($config);
        }
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addSubscribersFilter(DatagridConfiguration $config)
    {
        $filters = $config->offsetGetByPath('[filters][columns]', []);
        if (!array_key_exists('isSubscriber', $filters)) {
            $filters['isSubscriber'] = [
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
            ];
            $config->offsetSetByPath('[filters][columns]', $filters);
        }
    }

    /**
     * @param DatagridConfiguration $config
     */
    protected function addNewsletterSubscribers(DatagridConfiguration $config)
    {
        $query = $config->offsetGetByPath('[source][query]', []);
        foreach ($query['select'] as &$field) {
            if ($field === 'c.id') {
                $field = 'DISTINCT ' . $field;
                break;
            }
        }
        $query['select'][] = "CASE WHEN
                transport.isExtensionInstalled = true AND transport.extensionVersion IS NOT NULL
            THEN (CASE WHEN newsletterSubscriberStatus.id = '1' THEN 'yes' ELSE 'no' END)
            ELSE 'unknown'
            END as isSubscriber";

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
        $query['join']['left'][] = [
            'join' => 'newsletterSubscribers.status',
            'alias' => 'newsletterSubscriberStatus'
        ];
        $config->offsetSetByPath('[source][query]', $query);
    }
}
