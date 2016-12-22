<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Handler;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Form\Handler\ChannelHandler as IntegrationChannelHandler;
use Oro\Bundle\ChannelBundle\Form\Handler\ChannelIntegrationHandler;

class ChannelIntegrationHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NAME = 'name';

    /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var Integration */
    protected $entity;

    /** @var ChannelIntegrationHandler */
    protected $handler;

    /** @var FormFactoryInterface */
    protected $formBuilder;

    public function setUp()
    {
        $this->form        = $this->createMock('Symfony\Component\Form\Test\FormInterface');
        $this->formBuilder = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $this->formBuilder
            ->expects($this->any())
            ->method('createNamed')
            ->will($this->returnValue($this->form));

        $this->request = Request::create('');
        $this->entity  = new Integration();
        $this->handler = new ChannelIntegrationHandler($this->request, $this->formBuilder);
    }

    public function tearDown()
    {
        unset($this->handler, $this->request, $this->form, $this->entity);
    }

    public function testProcessUnsupportedRequest()
    {
        $this->form->expects($this->once())->method('setData')
            ->with($this->entity);

        $this->form->expects($this->never())->method('submit');
        $this->assertFalse($this->handler->process($this->entity));
    }

    public function testGetRequestHandling()
    {
        $data = ['name' => self::TEST_NAME];
        $this->request->setMethod('GET');
        $this->request->query->set(ChannelIntegrationHandler::DATA_PARAM_NAME, $data);

        $this->form->expects($this->once())->method('submit')
            ->with($this->equalTo($data));

        $this->assertFalse($this->handler->process($this->entity), 'Should not perform after submit actions');
        $this->assertTrue($this->request->get(IntegrationChannelHandler::UPDATE_MARKER), 'Should  set update marker');
    }

    /**
     * @dataProvider dataProvider
     *
     * @param bool $updateMarker
     * @param bool $isFormValid
     * @param bool $expectedResult
     */
    public function testPostRequestHandling($updateMarker, $isFormValid, $expectedResult)
    {
        $this->request->setMethod('POST');
        $this->request->query->set(IntegrationChannelHandler::UPDATE_MARKER, $updateMarker);

        $this->form->expects($this->once())->method('submit')
            ->with($this->equalTo($this->request));
        $this->form->expects($this->any())->method('isValid')
            ->will($this->returnValue($isFormValid));

        $this->assertSame($updateMarker, $this->request->get(IntegrationChannelHandler::UPDATE_MARKER));
        $this->assertSame($expectedResult, $this->handler->process($this->entity));
    }

    /**
     * @return array
     */
    public function dataProvider()
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
     *
     * @param string $requestType
     * @param array  $requestData
     * @param array  $expectedResult
     * @param null   $expectedException
     */
    public function testGetFormSubmittedData($requestType, $requestData, $expectedResult, $expectedException = null)
    {
        $this->request->setMethod($requestType);
        foreach ($requestData as $key => $value) {
            $this->request->request->set($key, $value);
        }
        if (null !== $expectedException) {
            $this->expectException($expectedException);
        }

        $this->form->expects($this->any())->method('getName')
            ->will($this->returnValue(self::TEST_NAME));

        $this->assertSame($expectedResult, $this->handler->getFormSubmittedData());
    }

    /**
     * @return array
     */
    public function submittedDataProvider()
    {
        return [
            'bad request, should throw exception' => [
                '$requestType'       => 'GET',
                '$requestData'       => [],
                '$expectedResult'    => [],
                '$expectedException' => '\LogicException'
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
     *
     * @param bool $isUpdateMode
     */
    public function testGetFormView($isUpdateMode)
    {
        $this->request->query->set(IntegrationChannelHandler::UPDATE_MARKER, $isUpdateMode);

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
            $formType->expects($this->once())->method('getName')
                ->will($this->returnValue('type' . self::TEST_NAME));
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
}
