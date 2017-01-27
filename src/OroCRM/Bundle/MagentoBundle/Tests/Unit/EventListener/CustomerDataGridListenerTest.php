<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use OroCRM\Bundle\MagentoBundle\EventListener\CustomerDataGridListener;

class CustomerDataGridListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerDataGridListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->listener = new CustomerDataGridListener();
    }

    public function testAddNewsletterSubscribersWhenFilteringByIsSubscriberWasNotRequested()
    {
        $parameters = new ParameterBag();

        $config = DatagridConfiguration::create(
            [
                'source' => [
                    'query' => [
                        'select' => [
                            'c.id',
                            'c.firstName'
                        ],
                        'from'   => [
                            ['table' => 'OroCRM\Bundle\MagentoBundle\Entity\Customer', 'alias' => 'c']
                        ]
                    ]
                ]
            ]
        );

        $this->listener->onPreBuild(new PreBuild($config, $parameters));

        $this->assertEquals(
            [
                'source'  => [
                    'query' => [
                        'select' => [
                            'c.id',
                            'c.firstName'
                        ],
                        'from'   => [
                            ['table' => 'OroCRM\Bundle\MagentoBundle\Entity\Customer', 'alias' => 'c']
                        ]
                    ]
                ],
                'filters' => [
                    'columns' => [
                        'isSubscriber' => [
                            'label'     => 'orocrm.magento.datagrid.columns.is_subscriber.label',
                            'type'      => 'single_choice',
                            'data_name' => 'isSubscriber',
                            'options'   => [
                                'field_options' => [
                                    'choices' => [
                                        'unknown' => 'orocrm.magento.datagrid.columns.is_subscriber.unknown',
                                        'no'      => 'orocrm.magento.datagrid.columns.is_subscriber.no',
                                        'yes'     => 'orocrm.magento.datagrid.columns.is_subscriber.yes'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $config->toArray()
        );
    }

    public function testAddNewsletterSubscribersWhenFilteringByIsSubscriberWasRequested()
    {
        $parameters = new ParameterBag();
        $parameters->set(
            '_filter',
            ['isSubscriber' => ['value' => 'yes']]
        );

        $config = DatagridConfiguration::create(
            [
                'source' => [
                    'query' => [
                        'select' => [
                            'c.id',
                            'c.firstName'
                        ],
                        'from'   => [
                            ['table' => 'OroCRM\Bundle\MagentoBundle\Entity\Customer', 'alias' => 'c']
                        ]
                    ]
                ]
            ]
        );

        $this->listener->onPreBuild(new PreBuild($config, $parameters));

        $this->assertEquals(
            [
                'source'  => [
                    'query' => [
                        'select'   => [
                            'DISTINCT c.id',
                            'c.firstName',
                            'CASE WHEN'
                            . ' transport.isExtensionInstalled = true AND transport.extensionVersion IS NOT NULL'
                            . ' THEN (CASE WHEN'
                            . ' IDENTITY(newsletterSubscribers.status) = \'1\' THEN \'yes\' ELSE \'no\' END)'
                            . ' ELSE \'unknown\''
                            . ' END as isSubscriber'
                        ],
                        'from'     => [
                            ['table' => 'OroCRM\Bundle\MagentoBundle\Entity\Customer', 'alias' => 'c']
                        ],
                        'join'     => [
                            'left' => [
                                [
                                    'join'  => 'c.channel',
                                    'alias' => 'channel'
                                ],
                                [
                                    'join'          => 'OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport',
                                    'alias'         => 'transport',
                                    'conditionType' => 'WITH',
                                    'condition'     => 'channel.transport = transport'
                                ],
                                [
                                    'join'  => 'c.newsletterSubscribers',
                                    'alias' => 'newsletterSubscribers'
                                ]
                            ]
                        ]
                    ]
                ],
                'filters' => [
                    'columns' => [
                        'isSubscriber' => [
                            'label'     => 'orocrm.magento.datagrid.columns.is_subscriber.label',
                            'type'      => 'single_choice',
                            'data_name' => 'isSubscriber',
                            'options'   => [
                                'field_options' => [
                                    'choices' => [
                                        'unknown' => 'orocrm.magento.datagrid.columns.is_subscriber.unknown',
                                        'no'      => 'orocrm.magento.datagrid.columns.is_subscriber.no',
                                        'yes'     => 'orocrm.magento.datagrid.columns.is_subscriber.yes'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $config->toArray()
        );
    }
}
