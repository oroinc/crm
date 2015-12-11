<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Handler;

use OroCRM\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

class B2bCustomerHandlerTest extends \PHPUnit_Framework_TestCase
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
     * @var B2bCustomerHandler
     */
    protected $handler;

    /**
     * @var B2bCustomer
     */
    protected $entity;

    /**
     * @var RequestChannelProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestChannelProvider;

    protected function setUp()
    {
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = new Request();

        $this->manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestChannelProvider
            = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\RequestChannelProvider')
            ->disableOriginalConstructor()->getMock();

        $this->entity  = new B2bCustomer();
        $this->handler = new B2bCustomerHandler(
            $this->form,
            $this->request,
            $this->manager,
            $this->requestChannelProvider
        );
    }

    public function testProcessUnsupportedRequest()
    {
        $this->requestChannelProvider->expects($this->once())
            ->method('setDataChannel')
            ->with($this->entity);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->never())
            ->method('submit');

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

        $this->form->expects($this->any())->method('setData')
            ->with($this->entity);
        $this->form->expects($this->once())->method('submit')
            ->with($this->request);
        $this->form->expects($this->once())->method('isValid')
            ->will($this->returnValue(true));

        $this->assertTrue($this->handler->process($this->entity));
    }

    public function supportedMethods()
    {
        return [
            ['POST'],
            ['PUT']
        ];
    }
}
