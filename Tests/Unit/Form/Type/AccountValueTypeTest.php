<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AccountBundle\Form\Type\AccountValueType;

class AccountValueTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $flexibleManager = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $type = new AccountValueType($flexibleManager, 'oro_account_value');
        $this->assertEquals('oro_account_value', $type->getName());
    }
}
