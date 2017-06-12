<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Datagrid;

use OroCRM\Bundle\MagentoBundle\Model\ChannelSettingsProvider;

abstract class AbstractTwoWaySyncActionPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ChannelSettingsProvider
     */
    protected $settingsProvider;

    protected function setUp()
    {
        $this->settingsProvider = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Model\ChannelSettingsProvider')
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
        return $this->createEntity('OroCRM\Bundle\ChannelBundle\Entity\Channel', $id);
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
