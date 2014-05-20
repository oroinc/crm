<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Utils;

use OroCRM\Bundle\MagentoBundle\Utils\ValidationUtils;

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
        $order = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Order');
        $order->expects($this->any())->method('getIncrementId')->will($this->returnValue(self::TEST_INCREMENT_ID));
        $cart = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Cart');
        $cart->expects($this->any())->method('getOriginId')->will($this->returnValue(self::TEST_ORIGIN_ID));
        $cartStatus = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\CartStatus', [], ['test']);

        return [
            'should take increment ID'                       => [$order, self::TEST_INCREMENT_ID],
            'should take origin ID'                          => [$cart, self::TEST_ORIGIN_ID],
            'should not provoke error, show default message' => [$cartStatus, self::TEST_DEFAULT_MESSAGE]
        ];
    }
}
