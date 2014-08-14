<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\Handler;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use OroCRM\Bundle\ChannelBundle\Form\Handler\ChannelHandler;

class ChannelHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var \PHPUnit_Framework_MockObject_MockObject|RegistryInterface */
    protected $registry;

    /** @var \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface */
    protected $dispatcher;

    /** @var ChannelHandler */
    protected $handler;

    /** @var Channel */
    protected $entity;

    protected function setUp()
    {
        $this->request    = new Request();
        $this->form       = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()->getMock();
        $this->em         = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $this->registry   = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');

        $this->entity  = new Channel();
        $this->handler = new ChannelHandler($this->request, $this->form, $this->registry, $this->dispatcher);
    }

    public function testProcessUnsupportedRequest()
    {
        $this->form->expects($this->once())->method('setData')
            ->with($this->entity);

        $this->form->expects($this->never())->method('submit');
        $this->dispatcher->expects($this->never())->method('dispatch');

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * @dataProvider supportedMethods
     *
     * @param string $method
     */
    public function testProcessSupportedRequest($method)
    {
        $this->request->setMethod($method);

        $this->form->expects($this->once())->method('setData')
            ->with($this->entity);
        $this->form->expects($this->once())->method('submit')
            ->with($this->request);
        $this->dispatcher->expects($this->never())->method('dispatch');

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * @return array
     */
    public function supportedMethods()
    {
        return [['POST', 'PUT']];
    }

    public function testProcessValidData()
    {
        $this->request->setMethod('POST');

        $this->form->expects($this->once())->method('setData')->with($this->entity);
        $this->form->expects($this->once())->method('submit')->with($this->request);
        $this->form->expects($this->once())->method('isValid')
            ->will($this->returnValue(true));

        $this->registry->expects($this->any())->method('getEntityManager') ->will($this->returnValue($this->em));
        $this->em->expects($this->once())->method('persist')->with($this->entity);
        $this->em->expects($this->once())->method('flush');

        $this->dispatcher->expects($this->once())->method('dispatch')
            ->with(
                $this->equalTo(ChannelSaveEvent::EVENT_NAME),
                $this->isInstanceOf('OroCRM\Bundle\ChannelBundle\Event\ChannelSaveEvent')
            );

        $this->assertTrue($this->handler->process($this->entity));
    }
}
