<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\EventListener;

use Symfony\Component\Form\FormEvent;

use OroCRM\Bundle\MagentoBundle\Form\EventListener\MetricsFormSubscriber;

class MetricsFormSubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected $widgetConfigs;
    protected $metricsFormSubscriber;

    public function setUp()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->any())
            ->method('trans')
            ->will($this->returnCallback(function ($id) {
                return $id;
            }));

        $this->widgetConfigs = $this->getMockBuilder('Oro\Bundle\DashboardBundle\Model\WidgetConfigs')
            ->disableOriginalConstructor()
            ->getMock();

        $this->metricsFormSubscriber = new MetricsFormSubscriber($this->widgetConfigs, $translator);
    }

    public function testPreSetShouldPrepareDataForForm()
    {
        $twigVariables = [
            'widgetDataItems' => [
                'revenue' => [
                    'label' => 'Revenue',
                ],
                'orders_number' => [
                    'label' => 'Orders number',
                ],
            ],
        ];

        $eventData = null;

        $expectedEventData = [
            'metrics' => [
                [
                    'id'    => 'revenue',
                    'label' => 'Revenue',
                    'show'  => true,
                    'order' => 1,
                ],
                [
                    'id'    => 'orders_number',
                    'label' => 'Orders number',
                    'show'  => true,
                    'order' => 2,
                ],
            ],
        ];

        $this->widgetConfigs->expects($this->once())
            ->method('getWidgetAttributesForTwig')
            ->with(MetricsFormSubscriber::WIDGET_NAME)
            ->will($this->returnValue($twigVariables));

        $form = $this->getMock('Symfony\Component\Form\FormInterface');

        $event = new FormEvent($form, $eventData);
        $this->metricsFormSubscriber->preSet($event);

        $this->assertEquals($expectedEventData, $event->getData());
    }

    public function testPreSetShouldUpdateStoredData()
    {
        $twigVariables = [
            'widgetDataItems' => [
                'revenue' => [
                    'label' => 'Revenue',
                ],
                'orders_number' => [
                    'label' => 'Orders number',
                ],
            ],
        ];

        $eventData = [
            'metrics' => [
                [
                    'id'    => 'revenue',
                    'label' => 'Revenue old label',
                    'show'  => false,
                    'order' => 2,
                ],
                [
                    'id'    => 'orders_number',
                    'label' => 'Orders number old label',
                    'show'  => true,
                    'order' => 1,
                ],
            ],
        ];

        $expectedEventData = [
            'metrics' => [
                [
                    'id'    => 'orders_number',
                    'label' => 'Orders number',
                    'show'  => true,
                    'order' => 1,
                ],
                [
                    'id'    => 'revenue',
                    'label' => 'Revenue',
                    'show'  => false,
                    'order' => 2,
                ],
            ],
        ];

        $this->widgetConfigs->expects($this->once())
            ->method('getWidgetAttributesForTwig')
            ->with(MetricsFormSubscriber::WIDGET_NAME)
            ->will($this->returnValue($twigVariables));

        $form = $this->getMock('Symfony\Component\Form\FormInterface');

        $event = new FormEvent($form, $eventData);
        $this->metricsFormSubscriber->preSet($event);

        $this->assertEquals($expectedEventData, $event->getData());
    }
}
