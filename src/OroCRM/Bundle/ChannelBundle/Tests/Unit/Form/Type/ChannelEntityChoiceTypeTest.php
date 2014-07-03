<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelEntityChoiceType;

class ChannelEntityChoiceTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChannelEntityChoiceType */
    protected $type;

    public function setUp()
    {
        $provider = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()->getMock();

        $this->type = new ChannelEntityChoiceType($provider);
    }

    public function tearDown()
    {
        unset($this->type);
    }

    public function testType()
    {
        $this->assertSame('orocrm_channel_entity_choice_form', $this->type->getName());
        $this->assertSame('genemu_jqueryselect2_choice', $this->type->getParent());

        $this->assertInstanceOf('Oro\Bundle\EntityBundle\Form\Type\EntityChoiceType', $this->type);
    }
}
