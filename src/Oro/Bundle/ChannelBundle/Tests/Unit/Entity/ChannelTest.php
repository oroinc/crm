<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Entity;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\EntityName;
use Oro\Bundle\IntegrationBundle\Entity\Channel as IntegrationChannel;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class ChannelTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testProperties()
    {
        $properties = [
            'id'                       => ['id', 1],
            'name'                     => ['name', 'Some name'],
            'owner'                    => ['owner', $this->createMock(Organization::class)],
            'dataSource'               => ['dataSource', $this->createMock(IntegrationChannel::class)],
            'dataSource nullable data' => ['dataSource', null],
            'status'                   => ['status', true],
            'customerIdentity'         => ['customerIdentity', $this->createMock(EntityName::class)],
            'channelType'              => ['channelType', 'Custom'],
            'createdAt'                => ['createdAt', new \DateTime()],
            'updatedAt'                => ['updatedAt', new \DateTime()],
        ];

        $entity = new Channel();
        self::assertPropertyAccessors($entity, $properties);
    }

    /**
     * @dataProvider entitiesDataProvider
     */
    public function testEntities(array $loadedNames, array $toSet, array $expectedResult)
    {
        $entity = new Channel();
        foreach ($loadedNames as $name) {
            $entity->getEntitiesCollection()->add(new EntityName($name));
        }

        $entity->setEntities($toSet);
        $this->assertSame($expectedResult, array_values($entity->getEntities()));
    }

    public function entitiesDataProvider(): array
    {
        return [
            'should add new entries'                               => [[], ['testEntity1'], ['testEntity1']],
            'should not duplicate existing'                        => [
                ['testEntity1'],
                ['testEntity1'],
                ['testEntity1']
            ],
            'should remove if not passed'                          => [['testEntity1'], [], []],
            'should save existing add and remove in the same time' => [
                ['testEntity1', 'testEntity2'],
                ['testEntity1', 'testEntity3'],
                ['testEntity1', 'testEntity3']
            ],
        ];
    }

    public function testPrePersist()
    {
        $entity = new Channel();
        $entity->prePersist();

        self::assertNotNull($entity->getCreatedAt());
        self::assertNotNull($entity->getUpdatedAt());
        self::assertEquals($entity->getCreatedAt(), $entity->getUpdatedAt());
        self::assertNotSame($entity->getCreatedAt(), $entity->getUpdatedAt());

        $existingCreatedAt = $entity->getCreatedAt();
        $existingUpdatedAt = $entity->getUpdatedAt();
        $entity->prePersist();
        self::assertSame($existingCreatedAt, $entity->getCreatedAt());
        self::assertNotSame($existingUpdatedAt, $entity->getUpdatedAt());
    }

    public function testPreUpdate()
    {
        $entity = new Channel();
        $entity->preUpdate();

        self::assertNotNull($entity->getUpdatedAt());

        $existingUpdatedAt = $entity->getUpdatedAt();
        $entity->preUpdate();
        self::assertNotSame($existingUpdatedAt, $entity->getUpdatedAt());
    }

    public function testToString()
    {
        $entity = new Channel();
        $this->assertSame('', (string)$entity);

        $name = 'test name';
        $entity->setName($name);
        $this->assertSame($name, (string)$entity);
    }
}
