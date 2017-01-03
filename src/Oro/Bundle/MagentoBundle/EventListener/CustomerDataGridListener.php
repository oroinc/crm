<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

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
                'label' => 'oro.magento.datagrid.columns.is_subscriber.label',
                'type' => 'single_choice',
                'data_name' => 'isSubscriber',
                'options' => [
                    'field_options' => [
                        'choices' => [
                            'unknown' => 'oro.magento.datagrid.columns.is_subscriber.unknown',
                            'no' => 'oro.magento.datagrid.columns.is_subscriber.no',
                            'yes' => 'oro.magento.datagrid.columns.is_subscriber.yes'
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
        $query = $config->getOrmQuery();
        $selects = $query->getSelect();
        foreach ($selects as &$field) {
            if ($field === 'c.id') {
                $field = 'DISTINCT ' . $field;
                $query->setSelect($selects);
                break;
            }
        }
        $query->addSelect(
            'CASE WHEN transport.isExtensionInstalled = true AND transport.extensionVersion IS NOT NULL'
            . ' THEN (CASE WHEN newsletterSubscriberStatus.id = \'1\' THEN \'yes\' ELSE \'no\' END)'
            . ' ELSE \'unknown\''
            . ' END as isSubscriber'
        );
        $query->addLeftJoin(
            'Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport',
            'transport',
            'WITH',
            'channel.transport = transport'
        );
        $query->addLeftJoin('c.newsletterSubscribers', 'newsletterSubscribers');
        $query->addLeftJoin('newsletterSubscribers.status', 'newsletterSubscriberStatus');
    }
}
