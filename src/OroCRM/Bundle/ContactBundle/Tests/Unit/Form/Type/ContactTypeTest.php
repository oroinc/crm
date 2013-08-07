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
        $this->type = new ContactType();
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

    public function testBuildForm()
    {
        $expectedFields = array(
            'namePrefix' => 'text',
            'firstName' => 'text',
            'lastName' => 'text',
            'nameSuffix' => 'text',
            'gender' => 'oro_gender',
            'title' => 'text',
            'birthday' => 'oro_date',
            'description' => 'textarea',
            'jobTitle' => 'text',
            'fax' => 'text',
            'skype' => 'text',
            'twitterUrl' => 'text',
            'facebookUrl' => 'text',
            'googlePlusUrl' => 'text',
            'linkedInUrl' => 'text',

            'source' => 'entity',
            'owner' => 'oro_user_select',
            'assignedTo' => 'oro_user_select',
            'reportsTo' => 'orocrm_contact_select',
            'email' => 'email',
            'phone' => 'text',
            'method' => 'entity',
            'tags' => 'oro_tag_select',
            'addresses' => 'oro_address_collection',
            'groups' => 'entity',
            'appendAccounts' => 'oro_entity_identifier',
            'removeAccounts' => 'oro_entity_identifier',
        );

        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Oro\Bundle\AddressBundle\Form\EventListener\AddressCollectionTypeSubscriber'));

        $counter = 0;
        foreach ($expectedFields as $fieldName => $formType) {
            $builder->expects($this->at($counter))
                ->method('add')
                ->with($fieldName, $formType)
                ->will($this->returnSelf());
            $counter++;
        }

        $this->type->buildForm($builder, array());
    }
}
