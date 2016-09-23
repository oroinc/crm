<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Validator;

use Oro\Bundle\ChannelBundle\Validator\ChannelIntegrationConstraint;

class ChannelIntegrationConstraintTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTargets()
    {
        $constraint = new ChannelIntegrationConstraint();
        $this->assertSame('class', $constraint->getTargets());
    }

    public function testValidatedBy()
    {
        $constraint = new ChannelIntegrationConstraint();
        $this->assertSame('oro_channel.validator.channel_integration', $constraint->validatedBy());
    }
}
