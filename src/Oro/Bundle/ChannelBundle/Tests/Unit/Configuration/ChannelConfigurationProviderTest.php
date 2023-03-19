<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Configuration;

use Oro\Bundle\ChannelBundle\Configuration\ChannelConfigurationProvider;
use Oro\Bundle\ChannelBundle\Entity\CustomerIdentity;
use Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Bundles\TestBundle1\TestBundle1;
use Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Bundles\TestBundle2\TestBundle2;
use Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\Customer;
use Oro\Component\Config\CumulativeResourceManager;
use Oro\Component\Config\Resolver\ResolverInterface;
use Oro\Component\Testing\TempDirExtension;

class ChannelConfigurationProviderTest extends \PHPUnit\Framework\TestCase
{
    use TempDirExtension;

    private ChannelConfigurationProvider $configurationProvider;

    protected function setUp(): void
    {
        $cacheFile = $this->getTempFile('ChannelConfigurationProvider');

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver->expects(self::any())
            ->method('resolve')
            ->willReturnArgument(0);

        $this->configurationProvider = new ChannelConfigurationProvider($cacheFile, false, $resolver);

        $bundle1 = new TestBundle1();
        $bundle2 = new TestBundle2();
        CumulativeResourceManager::getInstance()
            ->clear()
            ->setBundles([
                $bundle1->getName() => get_class($bundle1),
                $bundle2->getName() => get_class($bundle2)
            ]);
    }

    public function testGetEntities()
    {
        $this->assertEquals(
            [
                'Oro\Bundle\TestBundle1\Entity\Entity1' => [
                    'name'                   => 'Oro\Bundle\TestBundle1\Entity\Entity1',
                    'dependent'              => [
                        'Oro\Bundle\TestBundle1\Entity\Entity1Status',
                        'Oro\Bundle\TestBundle1\Entity\Entity1Reason'
                    ],
                    'dependencies'           => [
                        'Oro\Bundle\TestBundle1\Entity\Entity2',
                        'Oro\Bundle\TestBundle1\Entity\Entity3'
                    ],
                    'dependencies_condition' => 'OR',
                    'navigation_items'       => [
                        'application_menu.menu1.list'
                    ],
                    'belongs_to'             => [
                        'integration' => 'testIntegrationType'
                    ]
                ],
                'Oro\Bundle\TestBundle2\Entity\Entity'  => [
                    'name'                   => 'Oro\Bundle\TestBundle2\Entity\Entity',
                    'dependent'              => [
                        'Oro\Bundle\TestBundle2\Entity\EntityContact'
                    ],
                    'navigation_items'       => [
                        'application_menu.activities_tab.contact'
                    ],
                    'dependencies'           => [],
                    'dependencies_condition' => 'AND'
                ]
            ],
            $this->configurationProvider->getEntities()
        );
    }

    public function testGetDependentEntitiesMap()
    {
        $this->assertEquals(
            [
                'Oro\Bundle\TestBundle1\Entity\Entity1Status' => [
                    'Oro\Bundle\TestBundle1\Entity\Entity1'
                ],
                'Oro\Bundle\TestBundle1\Entity\Entity1Reason' => [
                    'Oro\Bundle\TestBundle1\Entity\Entity1'
                ],
                'Oro\Bundle\TestBundle2\Entity\EntityContact' => [
                    'Oro\Bundle\TestBundle2\Entity\Entity'
                ]
            ],
            $this->configurationProvider->getDependentEntitiesMap()
        );
    }

    public function testGetChannelTypes()
    {
        $this->assertEquals(
            [
                'test1' => [
                    'label'             => 'test1 type',
                    'entities'          => [
                        'Oro\Bundle\TestBundle1\Entity\Entity1',
                        'Oro\Bundle\TestBundle1\Entity\Entity2',
                        'Oro\Bundle\TestBundle1\Entity\Entity3'
                    ],
                    'integration_type'  => 'test',
                    'customer_identity' => Customer::class,
                    'lifetime_value'    => 'some_field',
                    'priority'          => 0,
                    'system'            => false
                ],
                'test2' => [
                    'label'             => 'test2 type',
                    'entities'          => [],
                    'customer_identity' => CustomerIdentity::class,
                    'priority'          => 0,
                    'system'            => false
                ]
            ],
            $this->configurationProvider->getChannelTypes()
        );
    }

    public function testGetCustomerEntities()
    {
        $this->assertEquals(
            [Customer::class, CustomerIdentity::class],
            $this->configurationProvider->getCustomerEntities()
        );
    }
}
