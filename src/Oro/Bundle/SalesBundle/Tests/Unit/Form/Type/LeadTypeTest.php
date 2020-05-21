<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\EmailCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\PhoneCollectionType;
use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumSelectType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Form\Type\CustomerType;
use Oro\Bundle\SalesBundle\Form\Type\LeadType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class LeadTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LeadType
     */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new LeadType();
    }

    public function testBuildForm()
    {
        $expectedFields = array(
            'name' => TextType::class,
            'status' => EnumSelectType::class,
            'namePrefix' => TextType::class,
            'firstName' => TextType::class,
            'middleName' => TextType::class,
            'lastName' => TextType::class,
            'nameSuffix' => TextType::class,
            'contact' => ContactSelectType::class,
            'jobTitle' => TextType::class,
            'phones' => PhoneCollectionType::class,
            'emails' => EmailCollectionType::class,
            'customerAssociation' => CustomerType::class,
            'companyName' => TextType::class,
            'website' => TextType::class,
            'numberOfEmployees' => IntegerType::class,
            'industry' => TextType::class,
            'addresses' => AddressCollectionType::class,
            'source' => EnumSelectType::class,
            'notes' => OroResizeableRichTextType::class,
            'twitter' => TextType::class,
            'linkedIn' => TextType::class,
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

        $this->type->buildForm($builder, ['data_class' => Lead::class]);
    }

    public function testName()
    {
        $this->assertEquals('oro_sales_lead', $this->type->getName());
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class' => 'Oro\Bundle\SalesBundle\Entity\Lead',
                ]
            );

        $this->type->configureOptions($resolver);
    }
}
