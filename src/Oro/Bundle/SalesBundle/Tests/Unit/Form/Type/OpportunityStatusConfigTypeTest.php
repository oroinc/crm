<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Metadata\MetadataFactory;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Audit\AuditManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigCache;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager as EntityConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigProviderBagMock;
use Oro\Bundle\EntityExtendBundle\Form\EventListener\EnumFieldConfigSubscriber;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendDbIdentifierNameGenerator;
use Oro\Bundle\SalesBundle\Form\Type\OpportunityStatusConfigType;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class OpportunityStatusConfigTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldPopulateProbabilityFieldsFromSystemConfig()
    {
        $configManager = $this->getConfigManager(
            [
                'won' => 1.0,
                'in_progress' => 0.1,
            ]
        );

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

        $formType = $this->getFormType($configManager);
        $formType->onPreSetData($event);

        $this->assertEquals($expectedData, $event->getData());
    }

    /**
     * @dataProvider eventDataProvider
     */
    public function testShouldSaveProbabilityMapToSystemConfig(array $eventData)
    {
        $configManager = $this->getConfigManager();
        $event = $this->getFormEvent($eventData);

        $expectedData = [
            'in_progress' => 0.2,
            'negotiation' => 0.8,
            'empty' => null,
        ];

        $configManager->expects($this->any())
            ->method('set')
            ->with($this->anything(), $this->equalTo($expectedData));

        $formType = $this->getFormType($configManager);
        $formType->onSubmit($event);
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
     * @return OpportunityStatusConfigType
     */
    protected function getFormType(ConfigManager $configManager)
    {
        return new OpportunityStatusConfigType(
            $this->getEntityConfigManager(),
            $configManager,
            $this->getEnumFieldConfigSubscriber()
        );
    }

    /**
     * @return EntityConfigManager
     */
    protected function getEntityConfigManager()
    {
        $configProvider = $this->getMockBuilder(ConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configProvider->expects($this->any())
            ->method('getScope')
            ->willReturn('enum');

        $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataFactory = $this->getMockBuilder(MetadataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $modelManager = $this->getMockBuilder(ConfigModelManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $auditManager = $this->getMockBuilder(AuditManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $configCache = $this->getMockBuilder(ConfigCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityConfigManager = new EntityConfigManager(
            $eventDispatcher,
            $metadataFactory,
            $modelManager,
            $auditManager,
            $configCache
        );

        $configProviderBag = new ConfigProviderBagMock();
        $configProviderBag->addProvider($configProvider);
        $entityConfigManager->setProviderBag($configProviderBag);

        /** @var EntityConfigManager $entityConfigManager */
        return $entityConfigManager;
    }

    /**
     * @param array $probabilities
     *
     * @return ConfigManager
     */
    protected function getConfigManager(array $probabilities = [])
    {
        $configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager->expects($this->any())
            ->method('get')
            ->willReturn($probabilities);

        /** @var ConfigManager $configManager */
        return $configManager;
    }

    /**
     * @param array $data
     *
     * @return FormEvent
     */
    protected function getFormEvent(array $data)
    {
        /* @var $form FormInterface|\PHPUnit\Framework\MockObject\MockObject*/
        $form = $this->createMock(FormInterface::class);

        return new FormEvent($form, $data);
    }

    /**
     * @return EnumFieldConfigSubscriber
     */
    protected function getEnumFieldConfigSubscriber()
    {
        $translator = $this->createMock('Symfony\Contracts\Translation\TranslatorInterface');
        $enumSynchronizer = $this->getMockBuilder('Oro\Bundle\EntityExtendBundle\Tools\EnumSynchronizer')
            ->disableOriginalConstructor()
            ->getMock();

        $translator->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));

        return new EnumFieldConfigSubscriber(
            $this->getEntityConfigManager(),
            $translator,
            $enumSynchronizer,
            $this->createMock(ExtendDbIdentifierNameGenerator::class)
        );
    }
}
