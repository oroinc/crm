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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ChannelHandlerTest extends \PHPUnit\Framework\TestCase
{
    const TEST_NAME = 'name';
    const FORM_DATA = ['field' => 'value'];

    /** @var \PHPUnit\Framework\MockObject\MockObject|FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry */
    protected $registry;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EventDispatcherInterface */
    protected $dispatcher;

    /** @var ChannelHandler */
    protected $handler;

    /** @var Channel */
    protected $entity;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManager */
    protected $em;

    protected function setUp(): void
    {
        $this->request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $this->form = $this->createMock(Form::class);
        $this->em = $this->createMock(EntityManager::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->entity = new Channel();
        $this->handler = new ChannelHandler($requestStack, $this->form, $this->registry, $this->dispatcher);
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
        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form->expects($this->once())->method('setData')
            ->with($this->entity);
        $this->form->expects($this->once())->method('submit')
            ->with(self::FORM_DATA);
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
        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');

        $this->form->expects($this->once())->method('setData')->with($this->entity);
        $this->form->expects($this->once())->method('submit')->with(self::FORM_DATA);
        $this->form->expects($this->once())->method('isValid')
            ->will($this->returnValue(true));

        $this->registry->expects($this->any())->method('getManager')->will($this->returnValue($this->em));
        $this->em->expects($this->once())->method('persist')->with($this->entity);
        $this->em->expects($this->once())->method('flush');

        $this->dispatcher->expects($this->once())->method('dispatch')
            ->with(
                $this->isInstanceOf('Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent'),
                $this->equalTo(ChannelSaveEvent::EVENT_NAME)
            );

        $this->assertTrue($this->handler->process($this->entity));
    }

    /**
     * @dataProvider formViewDataProvider
     *
     * @param bool $isUpdateMode
     */
    public function testGetFormView($isUpdateMode)
    {
        $this->request->query->set(ChannelHandler::UPDATE_MARKER, $isUpdateMode);

        $form = $this->form;
        if ($isUpdateMode) {
            $form        = $this->createMock('Symfony\Component\Form\Test\FormInterface');
            $formConfig  = $this->createMock('Symfony\Component\Form\FormConfigInterface');
            $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
            $formType    = $this->createMock('Symfony\Component\Form\ResolvedFormTypeInterface');

            $formConfig->expects($this->once())->method('getFormFactory')
                ->will($this->returnValue($formFactory));
            $formConfig->expects($this->once())->method('getType')
                ->will($this->returnValue($formType));
            $formType->expects($this->any())->method('getInnerType')
                ->will($this->returnValue(new FormStub('type' . self::TEST_NAME)));
            $this->form->expects($this->once())->method('getName')
                ->will($this->returnValue('form' . self::TEST_NAME));
            $this->form->expects($this->once())->method('getConfig')
                ->will($this->returnValue($formConfig));

            $formFactory->expects($this->once())->method('createNamed')
                ->will($this->returnValue($form));
        }

        $form->expects($this->once())->method('createView')
            ->will($this->returnValue($this->getFormView()));

        $this->assertInstanceOf('Symfony\Component\Form\FormView', $this->handler->getFormView());
    }

    /**
     * @return array
     */
    public function formViewDataProvider()
    {
        return [
            'update mode, should recreate form'       => ['$isUpdateMode' => true],
            'regular mode, should return origin form' => ['$isUpdateMode' => false],
        ];
    }

    /**
     * @return FormView
     */
    protected function getFormView()
    {
        $rootView       = new FormView();
        $connectorsView = new FormView($rootView);
        $typeView       = new FormView($rootView);

        $rootView->children['connectors'] = $connectorsView;
        $rootView->children['type']       = $typeView;

        return $rootView;
    }

    /**
     * @param Channel $entity
     * @param mixed $requestValue
     * @param string $expectedType
     *
     * @dataProvider handleRequestDataProvider
     */
    public function testHandleRequestChannelType(Channel $entity, $requestValue, $expectedType)
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

    /**
     * @return array
     */
    public function handleRequestDataProvider()
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
