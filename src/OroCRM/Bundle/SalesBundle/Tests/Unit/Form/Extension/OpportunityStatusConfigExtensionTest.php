<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Extension;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use OroCRM\Bundle\SalesBundle\Form\Extension\OpportunityStatusConfigExtension;

class OpportunityStatusConfigExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldPopulateProbabilityFieldsFromSystemConfig()
    {
        $configManager = $this->getConfigManagerMock([
            'won' => 1.0,
            'in_progress' => 0.1,
        ]);

        $event = $this->getFormEvent([
            'enum' => [
                'enum_options' => [
                    ['id' => 'won'],
                    ['id' => 'in_progress'],
                    ['id' => 'unknown'],
                ]
            ]
        ]);

        $expectedData = [
            'enum' => [
                'enum_options' => [
                    [
                        'id' => 'won',
                        'probability' => 1.0
                    ],
                    [
                        'id' => 'in_progress',
                        'probability' => 0.1,
                    ],
                    [
                        'id' => 'unknown'
                    ],
                ]
            ]
        ];

        $extension = $this->getFormExtension($configManager);
        $extension->onPreSetData($event);

        $this->assertEquals($expectedData, $event->getData());
    }

    /**
     * @dataProvider eventDataProvider
     */
    public function testShouldSaveProbabilityMapToSystemConfig(array $eventData)
    {
        $configManager = $this->getConfigManagerMock();
        $event = $this->getFormEvent($eventData);

        $expectedData = [
            'in_progress' => 0.2,
            'negotiation' => 0.8,
            'empty' => null,
        ];

        $configManager->expects($this->any())
            ->method('set')
            ->with($this->anything(), $this->equalTo($expectedData));

        $extension = $this->getFormExtension($configManager);
        $extension->onSubmit($event);
    }

    public function eventDataProvider()
    {
        return [
            [
                'eventData' => [
                    'enum' => [
                        'enum_options' => [
                            [
                                'id' => 'in_progress',
                                'label' => 'In Progress',
                                'probability' => 0.2
                            ],
                            [
                                'id' => 'negotiation',
                                'label' => 'Negotiation',
                                'probability' => 0.8
                            ],
                            [
                                'id' => 'empty',
                                'label' => 'empty',
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @param ConfigManager $configManager
     *
     * @return OpportunityStatusConfigExtension
     */
    private function getFormExtension(ConfigManager $configManager)
    {
        return new OpportunityStatusConfigExtension($configManager);
    }

    /**
     * @param array $probabilities
     *
     * @return ConfigManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getConfigManagerMock(array $probabilities = [])
    {
        $configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager->expects($this->any())
            ->method('get')
            ->willReturn($probabilities);

        return $configManager;
    }

    /**
     * @param array $data
     *
     * @return FormEvent
     */
    private function getFormEvent(array $data)
    {
        /* @var $form FormInterface|\PHPUnit_Framework_MockObject_MockObject*/
        $form = $this->getMock(FormInterface::class);

        return new FormEvent($form, $data);
    }
}
