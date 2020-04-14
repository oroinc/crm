<?php

namespace Oro\Bundle\MagentoBundle\Test\Unit\ImportExport\Serializer\Normalizer;

use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer\PaymentDetailsNormalizer;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PaymentDetailsNormalizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PaymentDetailsNormalizer
     */
    protected $normalizer;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FieldHelper
     */
    protected $fieldHelper;

    protected function setUp(): void
    {
        $this->fieldHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\Helper\FieldHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->normalizer = new PaymentDetailsNormalizer($this->fieldHelper);
    }

    protected function tearDown(): void
    {
        unset($this->fieldHelper, $this->normalizer);
    }

    /**
     * @param bool $expected
     * @param mixed $type
     *
     * @dataProvider denormalizatioDataProvider
     */
    public function testSupportsDenormalization($expected, $type)
    {
        $this->normalizer->setSupportedClass('Oro\Bundle\MagentoBundle\Entity\Order');

        $type = is_object($type) ? get_class($type) : gettype($type);

        $this->assertEquals($expected, $this->normalizer->supportsDenormalization([], $type));
    }

    /**
     * @return array
     */
    public function denormalizatioDataProvider()
    {
        return [
            [false, null],
            [false, false],
            [false, true],
            [false, 1],
            [false, 0],
            [false, ''],
            [false, 'test'],
            [false, new \stdClass()],
            [false, new Cart()],
            [true, new Order()]
        ];
    }

    /**
     * @param mixed $expected
     * @param array $data
     *
     * @dataProvider denormalizeDataProvider
     */
    public function testDenormalize($expected, array $data)
    {
        $this->fieldHelper->expects($this->once())->method('getFields')->willReturn(
            [['name' => 'incrementId'], ['name' => 'paymentDetails']]
        );

        $this->fieldHelper->expects($this->once())->method('setObjectValue')->will(
            $this->returnCallback(
                function ($result, $fieldName, $value) {
                    $propertyAccessor = PropertyAccess::createPropertyAccessor();
                    $propertyAccessor->setValue($result, $fieldName, $value);
                }
            )
        );

        /** @var Order $order */
        $order = $this->normalizer->denormalize($data, 'Oro\Bundle\MagentoBundle\Entity\Order');

        $this->assertEquals($expected, $order->getPaymentDetails());
    }

    /**
     * @return array
     */
    public function denormalizeDataProvider()
    {
        return [
            [null, ['incrementId' => 1]],
            [null, ['paymentDetails' => ['cc_type' => 'cc_type']]],
            [null, ['paymentDetails' => ['cc_last4' => 'cc_last4']]],
            ['Card [cc_type, cc_last4]', ['paymentDetails' => ['cc_type' => 'cc_type', 'cc_last4' => 'cc_last4']]],
            ['Card [cc_type, cc_last4]', ['paymentDetails' => ['cc_type' => ' cc_type ', 'cc_last4' => ' cc_last4 ']]]
        ];
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization([]));
    }
}
