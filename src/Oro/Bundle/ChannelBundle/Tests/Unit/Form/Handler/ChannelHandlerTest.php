<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use Oro\Bundle\ChannelBundle\Form\Handler\ChannelHandler;
use Oro\Component\Testing\Unit\Form\Type\Stub\FormStub;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ChannelHandlerTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_NAME = 'name';
    private const FORM_DATA = ['field' => 'value'];

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var Request */
    private $request;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $dispatcher;

    /** @var Channel */
    private $entity;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    /** @var ChannelHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->request = new Request();
        $this->form = $this->createMock(Form::class);
        $this->em = $this->createMock(EntityManager::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->entity = new Channel();

        $requestStack = new RequestStack();
        $requestStack->push($this->request);

        $this->handler = new ChannelHandler($requestStack, $this->form, $this->registry, $this->dispatcher);
    }

    public function testProcessUnsupportedRequest(): void
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->never())
            ->method('submit');
        $this->dispatcher->expects($this->never())
            ->method('dispatch');

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * @dataProvider supportedMethods
     */
    public function testProcessSupportedRequest(string $method): void
    {
        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->dispatcher->expects($this->never())
            ->method('dispatch');

        $this->assertFalse($this->handler->process($this->entity));
    }

    public function supportedMethods(): array
    {
        return [['POST', 'PUT']];
    }

    public function testProcessValidData(): void
    {
        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->form->expects($this->once())
            ->method('isValid')
            ->willReturn(true);

        $this->registry->expects($this->any())
            ->method('getManager')
            ->willReturn($this->em);
        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->entity);
        $this->em->expects($this->once())
            ->method('flush');

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(ChannelSaveEvent::class),
                ChannelSaveEvent::EVENT_NAME
            );

        $this->assertTrue($this->handler->process($this->entity));
    }

    /**
     * @dataProvider formViewDataProvider
     */
    public function testGetFormView(bool $isUpdateMode): void
    {
        $this->request->query->set(ChannelHandler::UPDATE_MARKER, $isUpdateMode);

        $form = $this->form;
        if ($isUpdateMode) {
            $form = $this->createMock(FormInterface::class);
            $formConfig = $this->createMock(FormConfigInterface::class);
            $formFactory = $this->createMock(FormFactoryInterface::class);
            $formType = $this->createMock(ResolvedFormTypeInterface::class);

            $formConfig->expects($this->once())
                ->method('getFormFactory')
                ->willReturn($formFactory);
            $formConfig->expects($this->once())
                ->method('getType')
                ->willReturn($formType);
            $formType->expects($this->any())
                ->method('getInnerType')
                ->willReturn(new FormStub('type' . self::TEST_NAME));
            $this->form->expects($this->once())
                ->method('getName')
                ->willReturn('form' . self::TEST_NAME);
            $this->form->expects($this->once())
                ->method('getConfig')
                ->willReturn($formConfig);

            $formFactory->expects($this->once())
                ->method('createNamed')
                ->willReturn($form);
        }

        $form->expects($this->once())
            ->method('createView')
            ->willReturn($this->getFormView());

        $this->assertInstanceOf(FormView::class, $this->handler->getFormView());
    }

    public function formViewDataProvider(): array
    {
        return [
            'update mode, should recreate form'       => ['$isUpdateMode' => true],
            'regular mode, should return origin form' => ['$isUpdateMode' => false],
        ];
    }

    private function getFormView(): FormView
    {
        $rootView = new FormView();
        $connectorsView = new FormView($rootView);
        $typeView = new FormView($rootView);

        $rootView->children['connectors'] = $connectorsView;
        $rootView->children['type'] = $typeView;

        return $rootView;
    }

    /**
     * @dataProvider handleRequestDataProvider
     */
    public function testHandleRequestChannelType(Channel $entity, ?string $requestValue, string $expectedType): void
    {
        $this->request->setMethod('GET');

        $expectedEntity = clone $entity;
        $expectedEntity->setChannelType($expectedType);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($expectedEntity);

        $this->form->expects($this->never())
            ->method('submit');
        $this->dispatcher->expects($this->never())
            ->method('dispatch');
        $this->request->request->set('oro_channel_form', ['channelType' => $requestValue]);

        $this->handler->process($entity);
    }

    public function handleRequestDataProvider(): array
    {
        $channel = new Channel();
        $channel->setChannelType('existing_type');

        return [
            'has type' => [$channel, null, 'existing_type'],
            'has not request value' => [$channel, null, 'existing_type'],
            'has request value' => [new Channel(), 'some_type', 'some_type'],
        ];
    }
}
