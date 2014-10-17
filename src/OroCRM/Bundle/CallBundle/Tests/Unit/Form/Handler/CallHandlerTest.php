<?php

namespace OroCRM\Bundle\CallBundle\Tests\Unit\Form\Handler;

use OroCRM\Bundle\CallBundle\Entity\Manager\CallActivityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;

use OroCRM\Bundle\CallBundle\Entity\Call;
use OroCRM\Bundle\CallBundle\Form\Handler\CallHandler;
use OroCRM\Bundle\CallBundle\Tests\Unit\Fixtures\Entity\TestPhoneHolderTarget;
use OroCRM\Bundle\CallBundle\Tests\Unit\Fixtures\Entity\TestTarget;

class CallHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager
     */
    protected $manager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CallActivityManager
     */
    protected $callActivityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityRoutingHelper
     */
    protected $entityRoutingHelper;

    /**
     * @var CallHandler
     */
    protected $handler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FormFactory
     */
    protected $formFactory;

    /**
     * @var Call
     */
    protected $entity;

    protected function setUp()
    {
        $this->form                = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request             = new Request();
        $this->manager             = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->callActivityManager = $this->getMockBuilder(
            'OroCRM\Bundle\CallBundle\Entity\Manager\CallActivityManager'
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityRoutingHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formFactory         = $this->getMockBuilder('Symfony\Component\Form\FormFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entity  = new Call();
        $this->handler = new CallHandler(
            'orocrm_call_form',
            'orocrm_call_form',
            $this->request,
            $this->manager,
            $this->callActivityManager,
            $this->entityRoutingHelper,
            $this->formFactory
        );
    }

    public function testProcessGetRequestWithoutTargetEntity()
    {
        $this->entityRoutingHelper->expects($this->never())
            ->method('getEntity');

        $this->formFactory->expects($this->once())
            ->method('createNamed')
            ->with('orocrm_call_form', 'orocrm_call_form', $this->entity, [])
            ->will($this->returnValue($this->form));

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($this->entity));
    }

    public function testProcessGetRequestWithNoPhoneHolderTargetEntity()
    {
        $targetEntity = new TestTarget(123);

        $this->request->query->set('entityClass', get_class($targetEntity));
        $this->request->query->set('entityId', $targetEntity->getId());

        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntity')
            ->with(get_class($targetEntity), $targetEntity->getId())
            ->will($this->returnValue($targetEntity));

        $this->formFactory->expects($this->once())
            ->method('createNamed')
            ->with('orocrm_call_form', 'orocrm_call_form', $this->entity, [])
            ->will($this->returnValue($this->form));

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($this->entity));
    }

    public function testProcessGetRequestWithPhoneHolderTargetEntity()
    {
        $targetEntity = new TestPhoneHolderTarget(123, ['phone1', 'phone2']);

        $this->request->query->set('entityClass', get_class($targetEntity));
        $this->request->query->set('entityId', $targetEntity->getId());

        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntity')
            ->with(get_class($targetEntity), $targetEntity->getId())
            ->will($this->returnValue($targetEntity));

        $this->formFactory->expects($this->once())
            ->method('createNamed')
            ->with(
                'orocrm_call_form',
                'orocrm_call_form',
                $this->entity,
                [
                    'phone_suggestions' => ['phone1', 'phone2'],
                    'phone_default'     => 'phone1'
                ]
            )
            ->will($this->returnValue($this->form));

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * @dataProvider supportedMethods
     *
     * @param string $method
     */
    public function testProcessInvalidData($method)
    {
        $this->formFactory->expects($this->once())
            ->method('createNamed')
            ->with('orocrm_call_form', 'orocrm_call_form', $this->entity, [])
            ->will($this->returnValue($this->form));

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->entityRoutingHelper->expects($this->never())
            ->method('getEntity');
        $this->entityRoutingHelper->expects($this->never())
            ->method('getEntityReference');
        $this->callActivityManager->expects($this->never())
            ->method('addAssociation');

        $this->request->setMethod($method);

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * @dataProvider supportedMethods
     *
     * @param string $method
     */
    public function testProcessValidDataWithoutTargetEntity($method)
    {
        $this->formFactory->expects($this->once())
            ->method('createNamed')
            ->with('orocrm_call_form', 'orocrm_call_form', $this->entity, [])
            ->will($this->returnValue($this->form));

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->entityRoutingHelper->expects($this->never())
            ->method('getEntity');
        $this->entityRoutingHelper->expects($this->never())
            ->method('getEntityReference');
        $this->callActivityManager->expects($this->never())
            ->method('addAssociation');

        $this->manager->expects($this->once())
            ->method('persist')
            ->with($this->entity);

        $this->manager->expects($this->once())
            ->method('flush');

        $this->request->setMethod($method);

        $this->assertTrue($this->handler->process($this->entity));
    }

    /**
     * @dataProvider supportedMethods
     *
     * @param string $method
     */
    public function testProcessValidDataWithTargetEntity($method)
    {
        $targetEntity = new TestTarget(123);

        $this->request->query->set('entityClass', get_class($targetEntity));
        $this->request->query->set('entityId', $targetEntity->getId());

        $this->formFactory->expects($this->once())
            ->method('createNamed')
            ->with('orocrm_call_form', 'orocrm_call_form', $this->entity, [])
            ->will($this->returnValue($this->form));

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->entityRoutingHelper->expects($this->never())
            ->method('getEntity');
        $this->entityRoutingHelper->expects($this->once())
            ->method('getEntityReference')
            ->with(get_class($targetEntity), $targetEntity->getId())
            ->will($this->returnValue($targetEntity));
        $this->callActivityManager->expects($this->once())
            ->method('addAssociation')
            ->with($this->identicalTo($this->entity), $this->identicalTo($targetEntity));

        $this->manager->expects($this->once())
            ->method('persist')
            ->with($this->entity);

        $this->manager->expects($this->once())
            ->method('flush');

        $this->request->setMethod($method);

        $this->assertTrue($this->handler->process($this->entity));
    }

    public function supportedMethods()
    {
        return array(
            array('POST'),
            array('PUT')
        );
    }
}
