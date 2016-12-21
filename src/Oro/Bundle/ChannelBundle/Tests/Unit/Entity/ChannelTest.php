<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\ChannelBundle\Entity\EntityName;

class ChannelTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'Oro\Bundle\ChannelBundle\Entity\Channel';
    }

    /**
     * {@inheritDoc}
     */
    public function getDataProvider()
    {
        $name             = 'Some name';
        $owner            = $this->createMock('Oro\Bundle\OrganizationBundle\Entity\Organization');
        $integration      = $this->createMock('Oro\Bundle\IntegrationBundle\Entity\Channel');
        $customerIdentity = $this->createMock('Oro\Bundle\ChannelBundle\Entity\EntityName', [], ['phone']);
        $status           = true;
        $channelType      = 'Custom';
        $someDateTime     = new \DateTime();

        return [
            'name'                     => ['name', $name, $name],
            'owner'                    => ['owner', $owner, $owner],
            'dataSource'               => ['dataSource', $integration, $integration],
            'dataSource nullable data' => ['dataSource', null, null],
            'status'                   => ['status', $status, $status],
            'customerIdentity'         => ['customerIdentity', $customerIdentity, $customerIdentity],
            'channelType'              => ['channelType', $channelType, $channelType],
            'createdAt'                => ['createdAt', $someDateTime, $someDateTime],
            'updatedAt'                => ['updatedAt', $someDateTime, $someDateTime]
        ];
    }

    /**
     * @dataProvider entitiesDataProvider
     *
     * @param array $loadedNames
     * @param array $toSet
     * @param array $expectedResult
     */
    public function testEntities($loadedNames, $toSet, $expectedResult)
    {
        $this->initEntitiesCollectionFromArray($this->entity->getEntitiesCollection(), $loadedNames);

        $this->entity->setEntities($toSet);
        $this->assertSame($expectedResult, array_values($this->entity->getEntities()));
    }

    /**
     * @return array
     */
    public function entitiesDataProvider()
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

    /**
     * @param Collection $collection
     * @param array      $entities
     *
     * @return Collection
     */
    protected function initEntitiesCollectionFromArray(Collection $collection, array $entities)
    {
        foreach ($entities as $entity) {
            $el = new EntityName($entity);
            $collection->add($el);
        }

        return $collection;
    }

    public function testToString()
    {
        $this->assertEmpty((string)$this->entity);

        $testName = uniqid('name');
        $this->entity->setName($testName);
        $this->assertSame($testName, $this->entity->getName());
    }

    public function testPrePersist()
    {
        $this->assertNull($this->entity->getCreatedAt());

        $this->entity->prePersist();

        $this->assertInstanceOf('DateTime', $this->entity->getCreatedAt());
        $this->assertLessThan(3, $this->entity->getCreatedAt()->diff(new \DateTime())->s);
    }

    public function testPreUpdate()
    {
        $this->assertNull($this->entity->getUpdatedAt());

        $this->entity->preUpdate();

        $this->assertInstanceOf('DateTime', $this->entity->getUpdatedAt());
        $this->assertLessThan(3, $this->entity->getUpdatedAt()->diff(new \DateTime())->s);
    }
}
