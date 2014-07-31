<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\Collection;

use OroCRM\Bundle\ChannelBundle\Entity\EntityName;

class ChannelTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\ChannelBundle\Entity\Channel';
    }

    /**
     * {@inheritDoc}
     */
    public function getDataProvider()
    {
        $name        = 'Some name';
        $owner       = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');
        $integration = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel');

        return [
            'name'        => ['name', $name, $name],
            'owner'       => ['owner', $owner, $owner],
            'dataSource'  => ['dataSource', $integration, $integration]
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
}
