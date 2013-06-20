<?php
namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\ContactBundle\Form\Type\ContactApiType;

class ContactApiTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactApiType
     */
    protected $type;

    /**
     * Setup test env
     */
    public function setUp()
    {
        $flexibleManager = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->type = new ContactApiType($flexibleManager, 'test');
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

    public function testAddEntityFields()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->any())
            ->method('add')
            ->will($this->returnSelf());
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Oro\Bundle\UserBundle\Form\EventListener\PatchSubscriber'));
        $this->type->addEntityFields($builder);
    }

    public function testGetName()
    {
        $this->assertEquals('contact', $this->type->getName());
    }
}
