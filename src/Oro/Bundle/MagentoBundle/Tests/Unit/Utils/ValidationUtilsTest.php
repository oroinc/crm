<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Utils;

use Oro\Bundle\MagentoBundle\Utils\ValidationUtils;

class ValidationUtilsTest extends \PHPUnit_Framework_TestCase
{
    const TEST_INCREMENT_ID    = '1000001232';
    const TEST_ORIGIN_ID       = 12;
    const TEST_DEFAULT_MESSAGE = 'Validation error';

    /**
     * @dataProvider entityProvider
     *
     * @param object $entity
     * @param string $shouldContain
     */
    public function testErrorPrefixGuess($entity, $shouldContain)
    {
        $this->assertContains((string)$shouldContain, ValidationUtils::guessValidationMessagePrefix($entity));
    }

    /**
     * @return array
     */
    public function entityProvider()
    {
        $order = $this->createMock('Oro\Bundle\MagentoBundle\Entity\Order');
        $order->expects($this->any())->method('getIncrementId')->will($this->returnValue(self::TEST_INCREMENT_ID));
        $cart = $this->createMock('Oro\Bundle\MagentoBundle\Entity\Cart');
        $cart->expects($this->any())->method('getOriginId')->will($this->returnValue(self::TEST_ORIGIN_ID));
        $cartStatus = $this->createMock('Oro\Bundle\MagentoBundle\Entity\CartStatus', [], ['test']);

        return [
            'should take increment ID'                       => [$order, self::TEST_INCREMENT_ID],
            'should take origin ID'                          => [$cart, self::TEST_ORIGIN_ID],
            'should not provoke error, show default message' => [$cartStatus, self::TEST_DEFAULT_MESSAGE]
        ];
    }

    /**
     * @dataProvider messageProvider
     *
     * @param string $exceptionMessage
     * @param string $expectedMessage
     */
    public function testSanitizeSecureInfo($exceptionMessage, $expectedMessage)
    {
        $sanitisedMessage = ValidationUtils::sanitizeSecureInfo($exceptionMessage);
        $this->assertEquals($expectedMessage, $sanitisedMessage);
    }

    /**
     * @return array
     */
    public function messageProvider()
    {
        return [
            'some other text' => [
                '$exceptionMessage' => 'some message text',
                '$expectedMessage'  => 'some message text'
            ],
            'sanitized exception message'       => [
                '$exceptionMessage' => '<?xml version="1.0" encoding="UTF-8"?>' .
                    '<SOAP-ENV:Body><ns1:login><username xsi:type="xsd:string">abc</username>' .
                    '<apiKey xsi:type="xsd:string">abcabc1</apiKey></ns1:login></SOAP-ENV:Body></SOAP-ENV:Envelope>',
                '$expectedMessage'  => '<?xml version="1.0" encoding="UTF-8"?>' .
                    '<SOAP-ENV:Body><ns1:login><username xsi:type="xsd:string">abc</username>' .
                    '<apiKey xsi:type="xsd:string">***</apiKey></ns1:login></SOAP-ENV:Body></SOAP-ENV:Envelope>'
            ]
        ];
    }
}
