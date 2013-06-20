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

    public function testAddEntityFields()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->at(1))
            ->method('add')
            ->with('multiAddress', 'oro_address_collection')
            ->will($this->returnSelf());
        $builder->expects($this->at(2))
            ->method('add')
            ->with('groups', 'entity')
            ->will($this->returnSelf());
        $builder->expects($this->at(3))
            ->method('add')
            ->with('appendAccounts', 'oro_entity_identifier')
            ->will($this->returnSelf());
        $builder->expects($this->at(4))
            ->method('add')
            ->with('removeAccounts', 'oro_entity_identifier')
            ->will($this->returnSelf());

        $this->type->addEntityFields($builder);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->any())
            ->method('add')
            ->will($this->returnSelf());
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Oro\Bundle\AddressBundle\Form\EventListener\AddressCollectionTypeSubscriber'));
        $this->type->buildForm($builder, array());
    }
}
