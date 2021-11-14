<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\ChannelBundle\Form\Handler\ChannelIntegrationHandler;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Form\Handler\ChannelHandler as IntegrationChannelHandler;
use Oro\Component\Testing\Unit\Form\Type\Stub\FormStub;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ChannelIntegrationHandlerTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_NAME = 'name';

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var Request */
    private $request;

    /** @var Integration */
    private $entity;

    /** @var ChannelIntegrationHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->form = $this->createMock(FormInterface::class);
        $this->request = Request::create('');
        $this->entity = new Integration();

        $formBuilder = $this->createMock(FormFactoryInterface::class);
        $formBuilder->expects($this->any())
            ->method('createNamed')
            ->willReturn($this->form);

        $requestStack = new RequestStack();
        $requestStack->push($this->request);

        $this->handler = new ChannelIntegrationHandler($requestStack, $formBuilder);
    }

    public function testProcessUnsupportedRequest(): void
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->never())
            ->method('handleRequest');
        $this->assertFalse($this->handler->process($this->entity));
    }

    public function testGetRequestHandling(): void
    {
        $data = ['name' => self::TEST_NAME];
        $this->request->setMethod('GET');
        $this->request->query->set(ChannelIntegrationHandler::DATA_PARAM_NAME, $data);

        $this->form->expects($this->once())
            ->method('submit')
            ->with($data);

        $this->assertFalse($this->handler->process($this->entity), 'Should not perform after submit actions');
        $this->assertTrue($this->request->get(IntegrationChannelHandler::UPDATE_MARKER), 'Should  set update marker');
    }

    /**
     * @dataProvider dataProvider
     */
    public function testPostRequestHandling(bool $updateMarker, bool $isFormValid, bool $expectedResult): void
    {
        $this->request->setMethod('POST');
        $this->request->query->set(IntegrationChannelHandler::UPDATE_MARKER, $updateMarker);

        $this->form->expects($this->once())
            ->method('handleRequest')
            ->with($this->identicalTo($this->request));
        $this->form->expects($this->any())
            ->method('isSubmitted')
            ->willReturn(true);
        $this->form->expects($this->any())
            ->method('isValid')
            ->willReturn($isFormValid);

        $this->assertSame($updateMarker, $this->request->get(IntegrationChannelHandler::UPDATE_MARKER));
        $this->assertSame($expectedResult, $this->handler->process($this->entity));
    }

    public function dataProvider(): array
    {
        return [
            'form is invalid'                           => [
                '$updateMarker'   => false,
                '$isFormValid'    => false,
                '$expectedResult' => false
            ],
            'form is valid, but this is update request' => [
                '$updateMarker'   => true,
                '$isFormValid'    => true,
                '$expectedResult' => true
            ],
            'form is valid, no update request flag'     => [
                '$updateMarker'   => false,
                '$isFormValid'    => true,
                '$expectedResult' => true
            ]
        ];
    }

    /**
     * @dataProvider submittedDataProvider
     */
    public function testGetFormSubmittedData(
        string $requestType,
        array $requestData,
        array $expectedResult,
        string $expectedException = null
    ): void {
        $this->request->setMethod($requestType);
        foreach ($requestData as $key => $value) {
            $this->request->request->set($key, $value);
        }
        if (null !== $expectedException) {
            $this->expectException($expectedException);
        }

        $this->form->expects($this->any())
            ->method('getName')
            ->willReturn(self::TEST_NAME);

        $this->assertSame($expectedResult, $this->handler->getFormSubmittedData());
    }

    public function submittedDataProvider(): array
    {
        return [
            'bad request, should throw exception' => [
                '$requestType'       => 'GET',
                '$requestData'       => [],
                '$expectedResult'    => [],
                '$expectedException' => \LogicException::class
            ],
            'valid request'                       => [
                '$requestType'    => 'POST',
                '$requestData'    => [self::TEST_NAME => ['testKey' => 'testValue']],
                '$expectedResult' => ['testKey' => 'testValue']
            ]
        ];
    }

    /**
     * @dataProvider formViewDataProvider
     */
    public function testGetFormView(bool $isUpdateMode): void
    {
        $this->request->query->set(IntegrationChannelHandler::UPDATE_MARKER, $isUpdateMode);

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
            $formType->expects($this->once())
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
}
