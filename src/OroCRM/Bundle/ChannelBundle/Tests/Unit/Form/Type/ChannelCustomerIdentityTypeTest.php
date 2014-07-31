<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelCustomerIdentityType;

class ChannelCustomerIdentityTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChannelCustomerIdentityType */
    protected $type;

    protected $provider_result = [
        [
            'name'         => 'OroCRM\Bundle\AcmeBundle\Entity\Test1',
            'label'        => 'label1',
            'plural_label' => 'plural label1',
            'icon'         => 'icon1'
        ],
        [
            'name'         => 'OroCRM\Bundle\AcmeBundle\Entity\Test2',
            'label'        => 'label2',
            'plural_label' => 'plural label2',
            'icon'         => 'icon2'
        ],
        [
            'name'         => 'OroCRM\Bundle\AcmeBundle\Entity\Test3',
            'label'        => 'label3',
            'plural_label' => 'plural label3',
            'icon'         => 'icon3'
        ]
    ];

    public function setUp()
    {
        $provider = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()->getMock();

        $provider->expects($this->any())
            ->method('getEntities')
            ->will($this->returnValue($this->provider_result));

        $this->type = new ChannelCustomerIdentityType($provider);
    }

    public function tearDown()
    {
        unset($this->type);
    }

    public function testType()
    {
        $this->assertSame('orocrm_channel_customer_identity_select_form', $this->type->getName());
        $this->assertSame('genemu_jqueryselect2_choice', $this->type->getParent());

        $this->assertInstanceOf('OroCRM\Bundle\ChannelBundle\Form\Type\ChannelCustomerIdentityType', $this->type);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMockBuilder('Symfony\Component\OptionsResolver\OptionsResolver')
            ->disableOriginalConstructor()->getMock();

        $provider = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityProvider')
            ->disableOriginalConstructor()->getMock();

        $provider->expects($this->once())
            ->method('getEntities')
            ->will($this->returnValue($this->provider_result));

        $type = new ChannelCustomerIdentityType($provider);
        $type->setDefaultOptions($resolver);
    }
}
