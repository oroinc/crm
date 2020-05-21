<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MagentoBundle\Form\Type\OrderAddressApiType;
use Oro\Bundle\MagentoBundle\Form\Type\OrderSelectType;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class OrderAddressApiTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var OrderAddressApiType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new OrderAddressApiType();
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
            'types'        => TranslatableEntityType::class,
            'fax'          => TextType::class,
            'owner'        => OrderSelectType::class,
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

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class'      => 'Oro\Bundle\MagentoBundle\Entity\OrderAddress',
                    'csrf_protection' => false,
                ]
            );

        $this->type->configureOptions($resolver);
    }
}
