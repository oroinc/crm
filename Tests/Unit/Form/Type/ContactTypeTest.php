<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\ContactBundle\Form\Type\ContactType;

class ContactTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactType
     */
    protected $type;

    protected function setUp()
    {
        $flexibleManager = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new ContactType($flexibleManager, 'orocrm_contact');
    }

    public function testSetDefaultOptions()
    {
        /** @var OptionsResolverInterface $resolver */
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with($this->isType('array'));
        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_contact', $this->type->getName());
    }
}
