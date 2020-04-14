<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MagentoBundle\Form\Type\CustomerAddressApiType;
use Oro\Bundle\MagentoBundle\Form\Type\CustomerSelectType;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CustomerAddressApiTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerAddressApiType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new CustomerAddressApiType();
    }

    protected function tearDown(): void
    {
        unset($this->type);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface'));

        $expectedFields = [
            'label'        => TextType::class,
            'street'       => TextType::class,
            'street2'      => TextType::class,
            'city'         => TextType::class,
            'postalCode'   => TextType::class,
            'regionText'   => TextType::class,
            'namePrefix'   => TextType::class,
            'firstName'    => TextType::class,
            'middleName'   => TextType::class,
            'lastName'     => TextType::class,
            'nameSuffix'   => TextType::class,
            'phone'        => TextType::class,
            'primary'      => CheckboxType::class,
            'country'      => TranslatableEntityType::class,
            'countryText'  => TextType::class,
            'region'       => TranslatableEntityType::class,
            'owner'        => CustomerSelectType::class,
            'types'        => TranslatableEntityType::class
        ];

        $builder->expects($this->exactly(count($expectedFields)))
            ->method('add');

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
}
