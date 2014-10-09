<?php

namespace OroCRM\Bundle\CallBundle\Tests\Unit\Entity\Manager;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;

use OroCRM\Bundle\CallBundle\Entity\Manager\CallActivityManager;
use OroCRM\Bundle\CallBundle\Tests\Unit\Fixtures\Entity\TestTarget;

class CallActivityManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $activityConfigProvider;

    /** @var CallActivityManager */
    private $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $call;

    /** @var TestTarget */
    private $target;

    protected function setUp()
    {
        $this->activityConfigProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->call = $this->getMock('OroCRM\Bundle\CallBundle\Entity\Call', ['addActivityTarget']);
        $this->target = new TestTarget();

        $this->manager = new CallActivityManager($this->activityConfigProvider);
    }

    public function testAddAssociation()
    {
        $targetClass = get_class($this->target);
        $callClass  = get_class($this->call);

        $config = new Config(new EntityConfigId('activity', $targetClass));
        $config->set('activities', [$callClass]);

        $this->activityConfigProvider->expects($this->once())
            ->method('hasConfig')
            ->with($targetClass)
            ->will($this->returnValue(true));
        $this->activityConfigProvider->expects($this->once())
            ->method('getConfig')
            ->with($targetClass)
            ->will($this->returnValue($config));

        $this->call->expects($this->once())
            ->method('addActivityTarget')
            ->with($this->identicalTo($this->target));

        $result = $this->manager->addAssociation($this->call, $this->target);

        $this->assertTrue($result);
    }

    public function testAddAssociationForNotConfigurableTarget()
    {
        $targetClass = get_class($this->target);
        $callClass  = get_class($this->call);

        $config = new Config(new EntityConfigId('activity', $targetClass));
        $config->set('activities', [$callClass]);

        $this->activityConfigProvider->expects($this->once())
            ->method('hasConfig')
            ->with($targetClass)
            ->will($this->returnValue(false));
        $this->activityConfigProvider->expects($this->never())
            ->method('getConfig');

        $this->call->expects($this->never())
            ->method('addActivityTarget');

        $result = $this->manager->addAssociation($this->call, $this->target);

        $this->assertFalse($result);
    }

    public function testAddAssociationForTargetWithoutCallAssociation()
    {
        $targetClass = get_class($this->target);

        $config = new Config(new EntityConfigId('activity', $targetClass));
        $config->set('activities', ['Test\Entity']);

        $this->activityConfigProvider->expects($this->once())
            ->method('hasConfig')
            ->with($targetClass)
            ->will($this->returnValue(true));
        $this->activityConfigProvider->expects($this->once())
            ->method('getConfig')
            ->with($targetClass)
            ->will($this->returnValue($config));

        $this->call->expects($this->never())
            ->method('addActivityTarget');

        $result = $this->manager->addAssociation($this->call, $this->target);

        $this->assertFalse($result);
    }

    public function testAddAssociationForTargetWithoutAnyAssociations()
    {
        $targetClass = get_class($this->target);

        $config = new Config(new EntityConfigId('activity', $targetClass));

        $this->activityConfigProvider->expects($this->once())
            ->method('hasConfig')
            ->with($targetClass)
            ->will($this->returnValue(true));
        $this->activityConfigProvider->expects($this->once())
            ->method('getConfig')
            ->with($targetClass)
            ->will($this->returnValue($config));

        $this->call->expects($this->never())
            ->method('addActivityTarget');

        $result = $this->manager->addAssociation($this->call, $this->target);

        $this->assertFalse($result);
    }
}
