<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Entity\Manager\AssociationManager;
use Oro\Bundle\SalesBundle\Provider\CustomerVirtualRelationProvider;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\CustomerStub;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\LeadStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerVirtualRelationProviderTest extends TestCase
{
    private ConfigProvider&MockObject $configProvider;
    private CustomerVirtualRelationProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->configProvider = $this->createMock(ConfigProvider::class);

        $associationManager = $this->createMock(AssociationManager::class);
        $associationManager->expects($this->any())
            ->method('getAssociationTargets')
            ->willReturn([CustomerStub::class => 'target_field']);

        $this->provider = new CustomerVirtualRelationProvider($associationManager, $this->configProvider);
        $this->provider->setSourceClass(LeadStub::class);
    }

    public function testIsVirtualRelationWithNotSupportedClass(): void
    {
        self::assertFalse($this->provider->isVirtualRelation('Some\Test\Class', 'testField'));
    }

    public function testIsVirtualRelationWithNotSupportedField(): void
    {
        self::assertFalse($this->provider->isVirtualRelation(LeadStub::class, 'testField'));
    }

    public function testIsVirtualRelation(): void
    {
        self::assertTrue($this->provider->isVirtualRelation(LeadStub::class, 'target_field'));
    }

    public function testGetVirtualRelationQuery(): void
    {
        self::assertEquals(
            [
                'join' => [
                    'left' => [
                        [
                            'join' => 'entity.customerAssociation',
                            'alias' => 'target_field_ca',
                            'conditionType' => 'WITH'
                        ],
                        [
                            'join' => 'target_field_ca.target_field',
                            'alias' => 'target_field',
                            'conditionType' => 'WITH'
                        ]
                    ]
                ]
            ],
            $this->provider->getVirtualRelationQuery(LeadStub::class, 'target_field')
        );
    }

    public function testGetVirtualRelationsOnNonSupportedClass(): void
    {
        self::assertEquals([], $this->provider->getVirtualRelations('Some\Test\Class'));
    }

    public function testGetVirtualRelations(): void
    {
        $customerConfig = new Config(
            new EntityConfigId('entity', CustomerStub::class),
            [
                'label' => 'customer label'
            ]
        );

        $this->configProvider->expects($this->once())
            ->method('getConfig')
            ->with(CustomerStub::class)
            ->willReturn($customerConfig);

        self::assertEquals(
            [
                'target_field' => [
                    'label' => 'customer label',
                    'relation_type' => 'manyToOne',
                    'related_entity_name' => CustomerStub::class,
                    'target_join_alias' => 'target_field',
                    'query' => [
                        'join' => [
                            'left' => [
                                [
                                    'join' => 'entity.customerAssociation',
                                    'alias' => 'target_field_ca',
                                    'conditionType' => 'WITH'
                                ],
                                [
                                    'join' => 'target_field_ca.target_field',
                                    'alias' => 'target_field',
                                    'conditionType' => 'WITH'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $this->provider->getVirtualRelations(LeadStub::class)
        );
    }

    public function testGetTargetJoinAlias(): void
    {
        self::assertEquals('test', $this->provider->getTargetJoinAlias('someClass', 'test'));
    }
}
