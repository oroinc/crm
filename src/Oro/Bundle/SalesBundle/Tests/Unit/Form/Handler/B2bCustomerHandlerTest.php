<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class B2bCustomerHandlerTest extends \PHPUnit\Framework\TestCase
{
    const FORM_DATA = ['field' => 'value'];

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ObjectManager
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
     * @var RequestChannelProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestChannelProvider;

    protected function setUp(): void
    {
        $this->form = $this->createMock(Form::class);
        $this->request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $this->manager = $this->createMock(ObjectManager::class);
        $this->requestChannelProvider = $this->createMock(RequestChannelProvider::class);

        $this->entity  = new B2bCustomer();
        $this->handler = new B2bCustomerHandler(
            $this->form,
            $requestStack,
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
        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form->expects($this->any())->method('setData')
            ->with($this->entity);
        $this->form->expects($this->once())->method('submit')
            ->with(self::FORM_DATA);
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
