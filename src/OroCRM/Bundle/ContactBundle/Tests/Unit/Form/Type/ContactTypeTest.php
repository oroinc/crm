<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Form\Type;

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
            'middleName' => 'text',
            'lastName' => 'text',
            'nameSuffix' => 'text',
            'gender' => 'oro_gender',
            'birthday' => 'oro_date',
            'description' => 'oro_resizeable_rich_text',
            'jobTitle' => 'text',
            'fax' => 'text',
            'skype' => 'text',
            'twitter' => 'text',
            'facebook' => 'text',
            'googlePlus' => 'text',
            'linkedIn' => 'text',
            'picture' => 'oro_image',

            'source' => 'translatable_entity',
            'assignedTo' => 'oro_user_organization_acl_select',
            'reportsTo' => 'orocrm_contact_select',
            'method' => 'translatable_entity',
            'addresses' => 'oro_address_collection',
            'emails' => 'oro_email_collection',
            'phones' => 'oro_phone_collection',
            'groups' => 'entity',
            'appendAccounts' => 'oro_entity_identifier',
            'removeAccounts' => 'oro_entity_identifier',
        );

        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

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
