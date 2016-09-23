<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\SalesBundle\Form\Type\LeadType;

class LeadTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var LeadType
     */
    protected $type;

    protected function setUp()
    {
        $this->type = new LeadType();
    }

    public function testBuildForm()
    {
        $expectedFields = array(
            'name' => 'text',
            'status' => 'oro_enum_select',
            'dataChannel' => 'oro_channel_select_type',
            'namePrefix' => 'text',
            'firstName' => 'text',
            'middleName' => 'text',
            'lastName' => 'text',
            'nameSuffix' => 'text',
            'contact' => 'oro_contact_select',
            'jobTitle' => 'text',
            'phones' => 'oro_phone_collection',
            'emails' => 'oro_email_collection',
            'customer' => 'oro_sales_b2bcustomer_select',
            'companyName' => 'text',
            'website' => 'url',
            'numberOfEmployees' => 'number',
            'industry' => 'text',
            'addresses' => 'oro_address_collection',
            'source' => 'oro_enum_select',
            'notes' => 'oro_resizeable_rich_text',
            'twitter' => 'text',
            'linkedIn' => 'text',
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

        $this->type->buildForm($builder, []);
    }

    public function testName()
    {
        $this->assertEquals('oro_sales_lead', $this->type->getName());
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class' => 'Oro\Bundle\SalesBundle\Entity\Lead',
                    'cascade_validation' => true,
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }
}
