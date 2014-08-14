<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelEntityType;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelEntityChoiceTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChannelEntityType */
    protected $type;

    /** @var SettingsProvider */
    protected $settingProvider;

    public function setUp()
    {
        $provider = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()->getMock();

        $settingProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();

        $this->type = new ChannelEntityType($provider, $settingProvider);
    }

    public function tearDown()
    {
        unset($this->type);
    }

    public function testType()
    {
        $this->assertSame('orocrm_channel_entities', $this->type->getName());
        $this->assertSame('hidden', $this->type->getParent());

        $this->assertInstanceOf('Symfony\Component\Form\AbstractType', $this->type);
    }
}
