<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Validator;

use OroCRM\Bundle\ChannelBundle\Validator\ChannelCustomerIdentityConstraint;

class ChannelCustomerIdentityConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTargets()
    {
        $constraint = new ChannelCustomerIdentityConstraint();
        $this->assertSame('class', $constraint->getTargets());
    }
}
