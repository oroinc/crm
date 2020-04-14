<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Datagrid;

use Oro\Bundle\MagentoBundle\Model\ChannelSettingsProvider;

abstract class AbstractTwoWaySyncActionPermissionProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ChannelSettingsProvider
     */
    protected $settingsProvider;

    protected function setUp(): void
    {
        $this->settingsProvider = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Model\ChannelSettingsProvider')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param null|int $id
     * @return object
     */
    protected function createChannelIntegrationEntity($id = null)
    {
        return $this->createEntity('Oro\Bundle\IntegrationBundle\Entity\Channel', $id);
    }

    /**
     * @param null|int $id
     * @return object
     */
    protected function createChannelEntity($id = null)
    {
        return $this->createEntity('Oro\Bundle\ChannelBundle\Entity\Channel', $id);
    }

    /**
     * @param string $class
     * @param int|null $id
     *
     * @return object
     */
    protected function createEntity($class, $id = null)
    {
        $entity = new $class();
        if ($id) {
            $reflection = new \ReflectionProperty($class, 'id');
            $reflection->setAccessible(true);
            $reflection->setValue($entity, $id);
        }

        return $entity;
    }
}
