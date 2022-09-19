<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class B2bCustomerHandlerTest extends \PHPUnit\Framework\TestCase
{
    private const FORM_DATA = ['field' => 'value'];

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var Request */
    private $request;

    /** @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject */
    private $manager;

    /** @var RequestChannelProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $requestChannelProvider;

    /** @var B2bCustomer */
    private $entity;

    /** @var B2bCustomerHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->form = $this->createMock(Form::class);
        $this->request = new Request();
        $this->manager = $this->createMock(ObjectManager::class);
        $this->requestChannelProvider = $this->createMock(RequestChannelProvider::class);
        $this->entity = new B2bCustomer();

        $this->handler = new B2bCustomerHandler(
            $this->manager,
            $this->requestChannelProvider
        );
    }

    public function testProcessUnsupportedRequest(): void
    {
        $this->requestChannelProvider->expects($this->once())
            ->method('setDataChannel')
            ->with($this->entity);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->never())
            ->method('submit');

        self::assertFalse($this->handler->process($this->entity, $this->form, $this->request));
    }

    /**
     * @dataProvider supportedMethods
     */
    public function testProcessSupportedRequest(string $method): void
    {
        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form->expects($this->any())
            ->method('setData')
            ->with($this->entity);
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        self::assertTrue($this->handler->process($this->entity, $this->form, $this->request));
    }

    public function supportedMethods(): array
    {
        return [
            ['POST'],
            ['PUT']
        ];
    }
}
