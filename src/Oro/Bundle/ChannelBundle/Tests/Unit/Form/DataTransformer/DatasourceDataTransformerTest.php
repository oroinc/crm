<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\DataTransformer;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\ChannelBundle\Form\DataTransformer\DatasourceDataTransformer;

class DatasourceDataTransformerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TYPE = 'testType';
    const TEST_NAME = 'testName';

    /** @var FormFactoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $formFactory;

    /** @var DatasourceDataTransformer */
    protected $transformer;

    public function setUp()
    {
        $this->formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $this->transformer = new DatasourceDataTransformer($this->formFactory);
    }

    public function tearDown()
    {
        unset($this->transformer, $this->formFactory);
    }

    /**
     * @dataProvider transformDataProvider
     *
     * @param mixed $data
     * @param mixed $expectedResult
     */
    public function testTransform($data, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->transformer->transform($data));
    }

    public function transformDataProvider()
    {
        $integration = new Integration();
        $integration->setType(self::TEST_TYPE);
        $integration->setName(self::TEST_NAME);

        return [
            'should return null if empty data given' => [
                '$data'           => null,
                '$expectedResult' => null
            ],
            'should return null if bad data given'   => [
                '$data'           => ['testBadData'],
                '$expectedResult' => null
            ],
            'should convert to expected array'       => [
                '$data'           => $integration,
                '$expectedResult' => [
                    'type'       => self::TEST_TYPE,
                    'data'       => null,
                    'identifier' => $integration,
                    'name'       => self::TEST_NAME
                ]
            ]
        ];
    }

    /**
     * @dataProvider reverseTransformDataProvider
     *
     * @param mixed       $data
     * @param mixed       $expectedResult
     * @param bool        $expectedSubmit
     * @param null|string $expectedException
     */
    public function testReverseTransform($data, $expectedResult, $expectedSubmit = false, $expectedException = null)
    {
        if (null !== $expectedException) {
            $this->expectException($expectedException);
        }

        $this->initializeMocks($expectedSubmit, $expectedException);

        if (!is_array($expectedResult)) {
            $this->assertSame($expectedResult, $this->transformer->reverseTransform($data));
        } else {
            $result = $this->transformer->reverseTransform($data);

            foreach ($expectedResult as $key => $value) {
                $this->assertSame($value, self::readAttribute($result, $key));
            }
        }
    }

    /**
     * @return array
     */
    public function reverseTransformDataProvider()
    {
        $integration = new Integration();

        return [
            'should return null if empty data given'          => [
                '$data'           => null,
                '$expectedResult' => null
            ],
            'should throw exception if bad data given'        => [
                '$data'              => new \stdClass(),
                '$expectedResult'    => null,
                '$expectedSubmit'    => false,
                '$expectedException' => 'Symfony\Component\Form\Exception\UnexpectedTypeException'
            ],
            'should thor exception if invalid data submitted' => [
                '$data'              => ['data' => new \stdClass(), 'identifier' => null],
                '$expectedResult'    => null,
                '$expectedSubmit'    => true,
                '$expectedException' => '\LogicException'
            ],
            'should bind data'                                => [
                '$data'           => [
                    'data'       => [
                        'name' => self::TEST_NAME,
                        'type' => self::TEST_TYPE
                    ],
                    'identifier' => null
                ],
                '$expectedResult' => [
                    'name' => self::TEST_NAME,
                    'type' => self::TEST_TYPE
                ],
                '$expectedSubmit' => true,
            ],
            'should bind on data that comes form setData'     => [
                '$data'           => [
                    'data'       => [
                        'name' => self::TEST_NAME,
                        'type' => self::TEST_TYPE
                    ],
                    'identifier' => $integration
                ],
                '$expectedResult' => $integration,
                '$expectedSubmit' => true,
            ]
        ];
    }

    /**
     * @param bool  $expectedSubmit
     * @param mixed $expectedException
     */
    private function initializeMocks($expectedSubmit, $expectedException)
    {
        if ($expectedSubmit) {
            $formMock = $this->createMock('Symfony\Component\Form\Test\FormInterface');

            $data = null;
            $this->formFactory->expects($this->once())
                ->method('create')
                ->with(
                    $this->equalTo('oro_integration_channel_form'),
                    $this->isInstanceOf('Oro\Bundle\IntegrationBundle\Entity\Channel'),
                    $this->equalTo(['csrf_protection' => false, 'disable_customer_datasource_types' => false])
                )
                ->will(
                    $this->returnCallback(
                        function ($type, $formData) use (&$data, $formMock) {
                            // capture form data
                            $data = $formData;

                            return $formMock;
                        }
                    )
                );

            $formMock->expects($this->once())->method('submit')
                ->will(
                    $this->returnCallback(
                        function ($submitted) use (&$data) {
                            // emulate submit
                            $accessor = PropertyAccess::createPropertyAccessor();

                            foreach ($submitted as $key => $value) {
                                $accessor->setValue($data, $key, $value);
                            }
                        }
                    )
                );
            $invalid = null != $expectedException;
            $formMock->expects($this->once())->method('isValid')
                ->will($this->returnValue(!$invalid));

            if ($invalid) {
                $formMock->expects($this->once())->method('getErrors')
                    ->will($this->returnValue([new FormError('message')]));
            }
        } else {
            $this->formFactory->expects($this->never())
                ->method('create');
        }
    }
}
