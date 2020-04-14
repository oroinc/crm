<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\DataGridBundle\Provider\SelectedFields\SelectedFieldsProviderInterface;
use Oro\Bundle\MagentoBundle\EventListener\CustomerDataGridListener;

class CustomerDataGridListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var SelectedFieldsProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $selectedFieldsFromFiltersProvider;

    /** @var CustomerDataGridListener */
    protected $listener;

    protected function setUp(): void
    {
        $this->selectedFieldsFromFiltersProvider = $this->createMock(SelectedFieldsProviderInterface::class);
        $this->listener = new CustomerDataGridListener($this->selectedFieldsFromFiltersProvider);
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
                            ['table' => 'Oro\Bundle\MagentoBundle\Entity\Customer', 'alias' => 'c']
                        ]
                    ]
                ]
            ]
        );

        $this->selectedFieldsFromFiltersProvider
            ->expects($this->once())
            ->method('getSelectedFields')
            ->with($config, $parameters)
            ->willReturn([]);

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
                            ['table' => 'Oro\Bundle\MagentoBundle\Entity\Customer', 'alias' => 'c']
                        ]
                    ]
                ],
                'filters' => [
                    'columns' => [
                        'isSubscriber' => [
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
                    ]
                ]
            ],
            $config->toArray()
        );
    }

    public function testAddNewsletterSubscribersWhenFilteringByIsSubscriberWasRequested()
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
                            ['table' => 'Oro\Bundle\MagentoBundle\Entity\Customer', 'alias' => 'c']
                        ]
                    ]
                ]
            ]
        );

        $this->selectedFieldsFromFiltersProvider
            ->expects($this->once())
            ->method('getSelectedFields')
            ->with($config, $parameters)
            ->willReturn(['isSubscriber']);

        $this->listener->onPreBuild(new PreBuild($config, $parameters));

        $this->assertEquals(
            [
                'source'  => [
                    'query' => [
                        'distinct' => true,
                        'select'   => [
                            'c.id',
                            'c.firstName',
                            'CASE WHEN'
                            . ' transport.isExtensionInstalled = true AND transport.extensionVersion IS NOT NULL'
                            . ' THEN (CASE WHEN'
                            . ' IDENTITY(newsletterSubscribers.status) = \'1\' THEN \'yes\' ELSE \'no\' END)'
                            . ' ELSE \'unknown\''
                            . ' END as isSubscriber'
                        ],
                        'from'     => [
                            ['table' => 'Oro\Bundle\MagentoBundle\Entity\Customer', 'alias' => 'c']
                        ],
                        'join'     => [
                            'left' => [
                                [
                                    'join'  => 'c.channel',
                                    'alias' => 'channel'
                                ],
                                [
                                    'join'          => 'Oro\Bundle\MagentoBundle\Entity\MagentoTransport',
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
                    ]
                ]
            ],
            $config->toArray()
        );
    }
}
