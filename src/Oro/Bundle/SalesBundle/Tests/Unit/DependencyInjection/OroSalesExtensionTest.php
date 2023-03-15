<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\SalesBundle\DependencyInjection\OroSalesExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroSalesExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();

        $extension = new OroSalesExtension();
        $extension->load([], $container);

        self::assertNotEmpty($container->getDefinitions());
        self::assertSame(
            [
                [
                    'settings' => [
                        'resolved' => true,
                        'lead_feature_enabled' => ['value' => true, 'scope' => 'app'],
                        'opportunity_feature_enabled' => ['value' => true, 'scope' => 'app'],
                        'default_opportunity_probabilities' => [
                            'value' => [
                                'in_progress' => 0,
                                'identification_alignment' => 0.1,
                                'needs_analysis' => 0.2,
                                'solution_development' => 0.5,
                                'negotiation' => 0.8,
                                'won' => 1,
                                'lost' => 0,
                            ],
                            'scope' => 'app'
                        ],
                        'display_relevant_opportunities' => ['value' => true, 'scope' => 'app'],
                    ]
                ]
            ],
            $container->getExtensionConfig('oro_sales')
        );

        self::assertEquals(
            [],
            $container->getDefinition('oro_sales.api.account_customer_association_provider')
                ->getArgument('$customerAssociationNames')
        );
    }

    public function testLoadWithCustomConfigs(): void
    {
        $container = new ContainerBuilder();

        $configs = [
            ['api' => ['customer_association_names' => ['Test\Entity1' => 'testField1']]],
            ['api' => ['customer_association_names' => ['Test\Entity2' => 'testField2']]]
        ];

        $extension = new OroSalesExtension();
        $extension->load($configs, $container);

        self::assertEquals(
            ['Test\Entity1' => 'testField1', 'Test\Entity2' => 'testField2'],
            $container->getDefinition('oro_sales.api.account_customer_association_provider')
                ->getArgument('$customerAssociationNames')
        );
    }
}
