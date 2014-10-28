<?php

namespace OroCRM\Bundle\TaskBundle\Unit\Form\Handler;

use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;

use OroCRM\Bundle\TaskBundle\Entity\Task;
use OroCRM\Bundle\TaskBundle\Form\Handler\TaskHandler;
use OroCRM\Bundle\TaskBundle\Tests\Unit\Fixtures\Entity\TestTarget;

class TaskHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager */
    protected $om;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ActivityManager */
    protected $activityManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityRoutingHelper */
    protected $entityRoutingHelper;

    /** @var TaskHandler */
    protected $handler;

    /** @var Task */
    protected $entity;

    protected function setUp()
    {
        $this->form                = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request             = new Request();
        $this->om                  = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->activityManager     = $this->getMockBuilder('Oro\Bundle\ActivityBundle\Manager\ActivityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityRoutingHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entity  = new Task();
        $this->handler = new TaskHandler(
            $this->form,
            $this->request,
            $this->om,
            $this->activityManager,
            $this->entityRoutingHelper
        );
    }

    public function testProcessGetRequestAssignNotUser()
    {
        $targetEntity = new TestTarget(123);
        $action       = 'assign';

        $this->entityRoutingHelper->expects($this->once())
            ->method('getAction')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue($action));
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntityClassName')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue(get_class($targetEntity)));
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntityId')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue($targetEntity->getId()));
        $this->entityRoutingHelper->expects($this->never())
            ->method('getEntity');

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse(
            $this->handler->process($this->entity)
        );
    }

    public function testProcessGetRequestAssignToUser()
    {
        $targetEntity = new User();
        $this->setId($targetEntity, 123);
        $action = 'assign';

        $this->entityRoutingHelper->expects($this->once())
            ->method('getAction')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue($action));
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntityClassName')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue(get_class($targetEntity)));
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntityId')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue($targetEntity->getId()));
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntity')
            ->with(get_class($targetEntity), $targetEntity->getId())
            ->will($this->returnValue($targetEntity));

        $ownerField = $this->getMock('Symfony\Component\Form\FormInterface');
        $ownerFieldConfig = $this->getMock('Symfony\Component\Form\FormConfigInterface');
        $ownerFieldType = $this->getMock('Symfony\Component\Form\ResolvedFormTypeInterface');
        $this->form->expects($this->once())
            ->method('get')
            ->with('owner')
            ->will($this->returnValue($ownerField));
        $ownerField->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($ownerFieldConfig));
        $ownerFieldConfig->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue([]));
        $ownerFieldConfig->expects($this->once())
            ->method('getType')
            ->will($this->returnValue($ownerFieldType));
        $ownerFieldType->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('some_type'));
        $this->form->expects($this->once())
            ->method('add')
            ->with('owner', 'some_type', ['read_only' => true]);

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse(
            $this->handler->process($this->entity)
        );
    }

    public function testProcessGetRequest()
    {
        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse(
            $this->handler->process($this->entity)
        );
    }

    /**
     * @dataProvider supportedMethods
     *
     * @param string $method
     */
    public function testProcessInvalidData($method)
    {
        $this->request->setMethod($method);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->identicalTo($this->entity));
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->identicalTo($this->request));
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));
        $this->om->expects($this->never())
            ->method('persist');
        $this->om->expects($this->never())
            ->method('flush');

        $this->assertFalse(
            $this->handler->process($this->entity)
        );
    }

    /**
     * @dataProvider supportedMethods
     *
     * @param string $method
     */
    public function testProcessValidDataWithoutTargetEntity($method)
    {
        $this->request->setMethod($method);

        $this->entityRoutingHelper->expects($this->once())
            ->method('getAction')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue(null));
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntityClassName')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue(null));
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntityId')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue(null));

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->identicalTo($this->entity));
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->identicalTo($this->request));
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
        $this->om->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($this->entity));
        $this->om->expects($this->once())
            ->method('flush');

        $this->assertTrue(
            $this->handler->process($this->entity)
        );
    }

    /**
     * @dataProvider supportedMethods
     *
     * @param string $method
     */
    public function testProcessValidDataWithTargetEntity($method)
    {
        $targetEntity = new TestTarget(123);
        $action       = 'assign';

        $this->request->setMethod($method);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->identicalTo($this->entity));
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->identicalTo($this->request));
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->entityRoutingHelper->expects($this->once())
            ->method('getAction')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue($action));
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntityClassName')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue(get_class($targetEntity)));
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntityId')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue($targetEntity->getId()));
        $this->entityRoutingHelper->expects($this->never())
            ->method('getEntityReference');

        $this->activityManager->expects($this->never())
            ->method('addActivityTarget');

        $this->om->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($this->entity));
        $this->om->expects($this->once())
            ->method('flush');

        $this->assertTrue(
            $this->handler->process($this->entity)
        );
    }

    /**
     * @dataProvider supportedMethods
     *
     * @param string $method
     */
    public function testProcessValidDataWithTargetEntityActivity($method)
    {
        $targetEntity = new TestTarget(123);
        $action       = 'activity';

        $this->request->setMethod($method);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->identicalTo($this->entity));
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->identicalTo($this->request));
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->entityRoutingHelper->expects($this->once())
            ->method('getAction')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue($action));
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntityClassName')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue(get_class($targetEntity)));
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntityId')
            ->with($this->identicalTo($this->request))
            ->will($this->returnValue($targetEntity->getId()));
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntityReference')
            ->with(get_class($targetEntity), $targetEntity->getId())
            ->will($this->returnValue($targetEntity));

        $this->activityManager->expects($this->once())
            ->method('addActivityTarget')
            ->with($this->identicalTo($this->entity), $this->identicalTo($targetEntity));

        $this->om->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($this->entity));
        $this->om->expects($this->once())
            ->method('flush');

        $this->assertTrue(
            $this->handler->process($this->entity)
        );
    }

    public function supportedMethods()
    {
        return array(
            array('POST'),
            array('PUT')
        );
    }

    /**
     * @param mixed $obj
     * @param mixed $val
     */
    protected function setId($obj, $val)
    {
        $class = new \ReflectionClass($obj);
        $prop  = $class->getProperty('id');
        $prop->setAccessible(true);

        $prop->setValue($obj, $val);
    }
}
