<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;

use OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub\OroRichTextTypeStub;
use OroCRM\Bundle\SalesBundle\Form\Type\LeadType;
use OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub\LeadEntityStub;

class LeadTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    protected function setUp()
    {
        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtensions($this->getTypeExtensions())
            ->getFormFactory();
    }

    public function testSubmitForm()
    {
        $leadExpectedStub = new LeadEntityStub();
        $leadExpectedStub->setName('Lead Name');
        $leadExpectedStub->setStatus(new \stdClass());
        $leadExpectedStub->setDataChannel($this->getEntity('OroCRM\Bundle\ChannelBundle\Entity\Channel', 1));
        $leadExpectedStub->setNamePrefix('namePrefix');
        $leadExpectedStub->setFirstName('firstName');
        $leadExpectedStub->setMiddleName('middleName');
        $leadExpectedStub->setLastName('lastName');
        $leadExpectedStub->setNameSuffix('nameSuffix');
        $leadExpectedStub->setContact($this->getEntity('OroCRM\Bundle\ContactBundle\Entity\Contact', 1));
        $leadExpectedStub->setJobTitle('jobTitle');
        $leadExpectedStub->setPhoneNumber('123123');
        $leadExpectedStub->setEmail('test@test.com');
        $leadExpectedStub->setCustomer($this->getEntity('OroCRM\Bundle\SalesBundle\Entity\B2bCustomer', 1));
        $leadExpectedStub->setCompanyName('companyName');
        $leadExpectedStub->setWebsite('http://www.abc.com');
        $leadExpectedStub->setNumberOfEmployees(50.0);
        $leadExpectedStub->setIndustry('industry');
        $leadExpectedStub->setAddress($this->getEntity('Oro\Bundle\AddressBundle\Entity\Address', 1));
        $leadExpectedStub->setSource(new \stdClass());

        $formData = [
            'name' => 'Lead Name',
            'status' => 1,
            'dataChannel' => 1,
            'namePrefix' => 'namePrefix',
            'firstName' => 'firstName',
            'middleName' => 'middleName',
            'lastName' => 'lastName',
            'nameSuffix' => 'nameSuffix',
            'contact' => 1,
            'jobTitle' => 'jobTitle',
            'phoneNumber' => '123123',
            'email' => 'test@test.com',
            'customer' => 1,
            'companyName' => 'companyName',
            'website' => 'www.abc.com',
            'numberOfEmployees' => 50,
            'industry' => 'industry',
            'address' => 1,
            'source' => 1,
            'notes' => ''
        ];

        $leadType = new LeadType();
        $leadType->setDataClass('OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub\LeadEntityStub');
        $form = $this->factory->create($leadType);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertTrue($form->isValid());
        $this->assertEquals($leadExpectedStub, $form->getData());
    }

    public function testName()
    {
        $leadType = new LeadType();
        $this->assertEquals('orocrm_sales_lead', $leadType->getName());
    }

    public function testSetDefaultOptions()
    {
        $dataClass = 'Class';
        $leadType = new LeadType();
        $leadType->setDataClass($dataClass);
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class' => $dataClass,
                    'cascade_validation' => true,
                ]
            );

        $leadType->setDefaultOptions($resolver);
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $oroResizeableRich = new OroResizeableRichTextType();
        $oroRichText = new OroRichTextTypeStub();

        $contactSelectType = new EntityType(
            [
                1 => $this->getEntity('OroCRM\Bundle\ContactBundle\Entity\Contact', 1),
            ],
            'orocrm_contact_select'
        );

        $channelSelectType = new EntityType(
            [
                1 => $this->getEntity('OroCRM\Bundle\ChannelBundle\Entity\Channel', 1),
            ],
            'orocrm_channel_select_type',
            ['entities' => []]
        );

        $b2bCustomerSelectType = new EntityType(
            [
                1 => $this->getEntity('OroCRM\Bundle\SalesBundle\Entity\B2bCustomer', 1),
            ],
            'orocrm_sales_b2bcustomer_select'
        );

        $addressType = new EntityType(
            [
                1 => $this->getEntity('Oro\Bundle\AddressBundle\Entity\Address', 1),
            ],
            'oro_address'
        );

        $enumSelectType = new EntityType(
            [
                1 => new \stdClass(),
            ],
            'oro_enum_select',
            ['enum_code' => '']
        );

        return [
            new PreloadedExtension(
                [
                    $enumSelectType->getName() => $enumSelectType,
                    $channelSelectType->getName() => $channelSelectType,
                    $contactSelectType->getName() => $contactSelectType,
                    $b2bCustomerSelectType->getName() => $b2bCustomerSelectType,
                    $addressType->getName() => $addressType,
                    $oroResizeableRich->getName() => $oroResizeableRich,
                    $oroRichText->getName() => $oroRichText
                ],
                []
            )
        ];
    }

    /**
     * @return array
     */
    protected function getTypeExtensions()
    {
        $validator = $this->getMock('\Symfony\Component\Validator\Validator\ValidatorInterface');
        $validator->method('validate')->will($this->returnValue(new ConstraintViolationList()));
        $formTypeExtension = new FormTypeValidatorExtension($validator);

        return [$formTypeExtension];
    }

    /**
     * @param string $className
     * @param int $id
     * @param string $primaryKey
     *
     * @return object
     */
    protected function getEntity($className, $id, $primaryKey = 'id')
    {
        static $entities = [];
        if (!isset($entities[$className])) {
            $entities[$className] = [];
        }
        if (!isset($entities[$className][$id])) {
            $entities[$className][$id] = new $className;
            $reflectionClass = new \ReflectionClass($className);
            $method = $reflectionClass->getProperty($primaryKey);
            $method->setAccessible(true);
            $method->setValue($entities[$className][$id], $id);
        }
        return $entities[$className][$id];
    }
}
