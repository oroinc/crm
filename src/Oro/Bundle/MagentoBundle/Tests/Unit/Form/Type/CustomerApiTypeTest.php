<?php


namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\AccountBundle\Form\Type\AccountSelectType;
use Oro\Bundle\AddressBundle\Form\Type\AddressCollectionType;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\MagentoBundle\Form\Type\CustomerApiType;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Oro\Bundle\UserBundle\Form\Type\GenderType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CustomerApiTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerApiType */
    protected $type;

    protected function setUp(): void
    {
        $this->type = new CustomerApiType();
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

        $builder->expects($this->exactly(2))
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface'));

        $expectedFields = [
            'namePrefix'   => TextType::class,
            'firstName'    => TextType::class,
            'middleName'   => TextType::class,
            'lastName'     => TextType::class,
            'nameSuffix'   => TextType::class,
            'gender'       => GenderType::class,
            'birthday'     => OroDateType::class,
            'email'        => TextType::class,
            'originId'     => TextType::class,
            'website'      => TranslatableEntityType::class,
            'store'        => TranslatableEntityType::class,
            'group'        => TranslatableEntityType::class,
            'dataChannel'  => TranslatableEntityType::class,
            'addresses'    => AddressCollectionType::class,
            'owner'        => TranslatableEntityType::class,
            'account'      => AccountSelectType::class
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
                    'data_class'      => 'Oro\Bundle\MagentoBundle\Entity\Customer',
                    'csrf_protection' => false,
                    'customer_association_disabled' => true
                ]
            );

        $this->type->configureOptions($resolver);
    }
}
