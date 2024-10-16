<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\SalesBundle\DependencyInjection\OroSalesExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OroSalesExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'prod');

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
                                'opportunity_status.in_progress'               => 0,
                                'opportunity_status.identification_alignment'  => 0.1,
                                'opportunity_status.needs_analysis'            => 0.2,
                                'opportunity_status.solution_development'      => 0.5,
                                'opportunity_status.negotiation'               => 0.8,
                                'opportunity_status.won'                       => 1,
                                'opportunity_status.lost'                      => 0,
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
        $container->setParameter('kernel.environment', 'prod');

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
