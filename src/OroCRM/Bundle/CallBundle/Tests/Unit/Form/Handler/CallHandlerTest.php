<?php

namespace OroCRM\Bundle\CallBundle\Tests\Unit\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;

use OroCRM\Bundle\CallBundle\Entity\Call;
use OroCRM\Bundle\CallBundle\Form\Handler\CallHandler;

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
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = new Request();
        $this->manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $callActivityManager = $this->getMockBuilder('OroCRM\Bundle\CallBundle\Entity\Manager\CallActivityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityRoutingHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formFactory = $this->getMockBuilder('Symfony\Component\Form\FormFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entity  = new Call();
        $this->handler = new CallHandler(
            "orocrm_call_form",
            "orocrm_call_form",
            $this->request,
            $this->manager,
            $callActivityManager,
            $this->entityRoutingHelper,
            $this->formFactory
        );
    }

    public function testProcessUnsupportedRequest()
    {
        $this->entityRoutingHelper->expects($this->once())
            ->method('decodeClassName')
            ->will($this->returnValue(null));

        $this->formFactory->expects($this->once())
            ->method('createNamed')
            ->will($this->returnValue($this->form));

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * @dataProvider supportedMethods
     * @param string $method
     */
    public function testProcessSupportedRequest($method)
    {
        $this->entityRoutingHelper->expects($this->once())
            ->method('decodeClassName')
            ->will($this->returnValue(null));

        $this->formFactory->expects($this->once())
            ->method('createNamed')
            ->will($this->returnValue($this->form));

        $this->request->setMethod($method);

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->assertFalse($this->handler->process($this->entity));
    }

    public function supportedMethods()
    {
        return array(
            array('POST'),
            array('PUT')
        );
    }

    public function testProcessValidData()
    {
        $this->request->setMethod('POST');

        $this->entityRoutingHelper->expects($this->once())
            ->method('decodeClassName')
            ->will($this->returnValue(null));

        $this->formFactory->expects($this->once())
            ->method('createNamed')
            ->will($this->returnValue($this->form));

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->manager->expects($this->once())
            ->method('persist')
            ->with($this->entity);

        $this->manager->expects($this->once())
            ->method('flush');

        $this->assertTrue($this->handler->process($this->entity));
    }
}
