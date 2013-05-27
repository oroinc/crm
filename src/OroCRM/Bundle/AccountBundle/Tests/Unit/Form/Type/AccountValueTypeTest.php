<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\AccountBundle\Form\Type\AccountValueType;

class AccountValueTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $flexibleManager = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $subscriber = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventSubscriberInterface')
            ->getMock();

        $type = new AccountValueType($flexibleManager, $subscriber);
        $this->assertEquals('orocrm_account_value', $type->getName());
    }
}
