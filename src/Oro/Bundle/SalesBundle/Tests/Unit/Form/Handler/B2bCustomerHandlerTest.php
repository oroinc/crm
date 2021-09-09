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

    private \PHPUnit\Framework\MockObject\MockObject|FormInterface $form;

    private Request $request;

    private \PHPUnit\Framework\MockObject\MockObject|ObjectManager $manager;

    private B2bCustomerHandler $handler;

    private B2bCustomer $entity;

    private RequestChannelProvider|\PHPUnit\Framework\MockObject\MockObject $requestChannelProvider;

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

        self::assertFalse($this->handler->process($this->entity));
    }

    /**
     * @dataProvider supportedMethods
     */
    public function testProcessSupportedRequest(string $method): void
    {
        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form->expects($this->any())->method('setData')
            ->with($this->entity);
        $this->form->expects($this->once())->method('submit')
            ->with(self::FORM_DATA);
        $this->form->expects($this->once())->method('isValid')
            ->willReturn(true);

        self::assertTrue($this->handler->process($this->entity));
    }

    public function supportedMethods(): array
    {
        return [
            ['POST'],
            ['PUT']
        ];
    }
}
