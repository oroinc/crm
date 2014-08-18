<?php
namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\ContactBundle\Form\Type\ContactSelectType;

class ContactSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactSelectType
     */
    protected $type;

    /**
     * Setup test env
     */
    protected function setUp()
    {
        $this->type = new ContactSelectType();
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

    public function testGetParent()
    {
        $this->assertEquals('oro_entity_create_or_select_inline', $this->type->getParent());
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_contact_select', $this->type->getName());
    }
}
