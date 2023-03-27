<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Audit\AuditManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigCache;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager as EntityConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\ConfigModelManager;
use Oro\Bundle\EntityConfigBundle\Metadata\Factory\MetadataFactory;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProviderBag;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\EntityConfig\Mock\ConfigurationHandlerMock;
use Oro\Bundle\EntityExtendBundle\Form\EventListener\EnumFieldConfigSubscriber;
use Oro\Bundle\EntityExtendBundle\Tools\EnumSynchronizer;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendDbIdentifierNameGenerator;
use Oro\Bundle\SalesBundle\Form\Type\OpportunityStatusConfigType;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class OpportunityStatusConfigTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldPopulateProbabilityFieldsFromSystemConfig()
    {
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->once())
            ->method('get')
            ->willReturn(['won' => 1.0, 'in_progress' => 0.1]);

        $event = new FormEvent($this->createMock(FormInterface::class), [
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

        $formType = new OpportunityStatusConfigType(
            $this->getEntityConfigManager(),
            $configManager,
            $this->getEnumFieldConfigSubscriber()
        );
        $formType->onPreSetData($event);

        $this->assertEquals($expectedData, $event->getData());
    }

    /**
     * @dataProvider eventDataProvider
     */
    public function testShouldSaveProbabilityMapToSystemConfig(array $eventData)
    {
        $expectedData = [
            'in_progress' => 0.2,
            'negotiation' => 0.8,
            'empty' => null,
        ];

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->once())
            ->method('set')
            ->with($this->anything(), $expectedData);

        $formType = new OpportunityStatusConfigType(
            $this->getEntityConfigManager(),
            $configManager,
            $this->getEnumFieldConfigSubscriber()
        );
        $formType->onSubmit(new FormEvent($this->createMock(FormInterface::class), $eventData));
    }

    public function eventDataProvider(): array
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

    private function getEntityConfigManager(): EntityConfigManager
    {
        $configProvider = $this->createMock(ConfigProvider::class);
        $configProvider->expects($this->any())
            ->method('getScope')
            ->willReturn('enum');

        $configProviderBag = $this->createMock(ConfigProviderBag::class);
        $configProviderBag->expects($this->any())
            ->method('getProvider')
            ->willReturnCallback(function ($scope) use ($configProvider) {
                return 'enum' === $scope
                    ? $configProvider
                    : null;
            });

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $metadataFactory = $this->createMock(MetadataFactory::class);
        $modelManager = $this->createMock(ConfigModelManager::class);
        $auditManager = $this->createMock(AuditManager::class);
        $configCache = $this->createMock(ConfigCache::class);
        $serviceProvider = new ServiceLocator([
            'annotation_metadata_factory' => function () use ($metadataFactory) {
                return $metadataFactory;
            },
            'configuration_handler' => function () {
                return ConfigurationHandlerMock::getInstance();
            },
            'event_dispatcher' => function () use ($eventDispatcher) {
                return $eventDispatcher;
            },
            'audit_manager' => function () use ($auditManager) {
                return $auditManager;
            },
            'config_model_manager' => function () use ($modelManager) {
                return $modelManager;
            }
        ]);
        $entityConfigManager = new EntityConfigManager(
            $configCache,
            $serviceProvider
        );
        $entityConfigManager->setProviderBag($configProviderBag);

        return $entityConfigManager;
    }

    private function getEnumFieldConfigSubscriber(): EnumFieldConfigSubscriber
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $enumSynchronizer = $this->createMock(EnumSynchronizer::class);

        $translator->expects($this->any())
            ->method('trans')
            ->willReturnArgument(0);

        return new EnumFieldConfigSubscriber(
            $this->getEntityConfigManager(),
            $translator,
            $enumSynchronizer,
            $this->createMock(ExtendDbIdentifierNameGenerator::class)
        );
    }
}
