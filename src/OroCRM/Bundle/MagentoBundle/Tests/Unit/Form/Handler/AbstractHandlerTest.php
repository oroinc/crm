<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\RegistryInterface;

abstract class AbstractHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $form;

    /** @var Request|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var RegistryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $manager;

    /** Object */
    protected $entity;

    /** should be redeclare */
    protected $handler;

    public function testProcessUnsupportedRequestType()
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $this->form->expects($this->never())
            ->method('submit');

        $this->form->expects($this->never())
            ->method('isValid');

        $this->manager->expects($this->never())
            ->method('persist');

        $this->manager->expects($this->never())
            ->method('flush');

        $this->assertFalse($this->handler->process($this->entity));
    }

    public function testProcessInvalidForm()
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $this->form->expects($this->once())
            ->method('submit');

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->manager->expects($this->never())
            ->method('persist');

        $this->manager->expects($this->never())
            ->method('flush');

        $this->assertFalse($this->handler->process($this->entity));
    }

    public function testProcess()
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $this->form->expects($this->once())
            ->method('submit');

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
