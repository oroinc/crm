<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Form\EventListener\AddressCountryAndRegionSubscriber;
use Oro\Bundle\AddressBundle\Form\EventListener\AddressIdentifierSubscriber;
use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\AddressBundle\Form\Type\CountryType;
use Oro\Bundle\AddressBundle\Form\Type\EmailCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\PhoneCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\RegionType;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\FormBundle\Form\Extension\AdditionalAttrExtension;
use Oro\Bundle\FormBundle\Form\Type\Select2EntityType;
use Oro\Bundle\FormBundle\Tests\Unit\Stub\StripTagsExtensionStub;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerPhone;
use Oro\Bundle\SalesBundle\Form\Type\B2bCustomerType;
use Oro\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub\EmailCollectionTypeParent;
use Oro\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub\EmailCollectionTypeStub;
use Oro\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub\PhoneCollectionTypeParent;
use Oro\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub\PhoneCollectionTypeStub;
use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityTypeStub;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

class B2bCustomerTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /** @var B2bCustomerType */
    private $formType;

    protected function setUp(): void
    {
        $this->formType = new B2bCustomerType(PropertyAccess::createPropertyAccessor());
        parent::setUp();
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtensions(): array
    {
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->createMock(ObjectRepository::class));

        return [
            new PreloadedExtension(
                [
                    $this->formType,
                    ContactSelectType::class => new EntityTypeStub([
                        1 => $this->getEntity(Contact::class, ['first_name' => 'first name']),
                        2 => $this->getEntity(Contact::class, ['first_name' => 'first name new']),
                    ]),
                    EmailCollectionType::class => new EmailCollectionTypeStub(),
                    EmailCollectionTypeParent::class => new EntityTypeStub([
                        1 => $this->getEntity(B2bCustomerEmail::class, ['email' => 'test@email.com']),
                        2 => $this->getEntity(B2bCustomerEmail::class, ['email' => 'test_new@email.com']),
                    ]),
                    PhoneCollectionType::class => new PhoneCollectionTypeStub(),
                    PhoneCollectionTypeParent::class => new EntityTypeStub([
                        1 => $this->getEntity(B2bCustomerPhone::class, ['phone' => '12345678']),
                        2 => $this->getEntity(B2bCustomerPhone::class, ['phone' => '87654321'])
                    ]),
                    ChannelSelectType::class => new ChannelSelectType(
                        $this->createMock(ChannelsByEntitiesProvider::class)
                    ),
                    Select2EntityType::class => new EntityTypeStub(),
                    new AddressType(
                        new AddressCountryAndRegionSubscriber(
                            $objectManager,
                            $this->createMock(FormFactoryInterface::class)
                        ),
                        new AddressIdentifierSubscriber()
                    ),
                    CountryType::class => new EntityTypeStub(),
                    RegionType::class => new EntityTypeStub(),
                ],
                [
                    FormType::class => [
                        new AdditionalAttrExtension(),
                        new StripTagsExtensionStub($this)
                    ]
                ]
            )
        ];
    }

    public function testGetBlockPrefix()
    {
        $this->assertEquals('oro_sales_b2bcustomer', $this->formType->getBlockPrefix());
    }

    public function testBuildForm()
    {
        $form = $this->factory->create(B2bCustomerType::class);

        $this->assertTrue($form->has('name'));
        $this->assertTrue($form->has('contact'));
        $this->assertTrue($form->has('emails'));
        $this->assertTrue($form->has('phones'));
        $this->assertTrue($form->has('dataChannel'));
        $this->assertTrue($form->has('shippingAddress'));
        $this->assertTrue($form->has('billingAddress'));
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmit(B2bCustomer $existingData, array $submittedData, B2bCustomer $expectedData)
    {
        $form = $this->factory->create(B2bCustomerType::class, $existingData);
        $this->assertEquals($existingData, $form->getData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());
        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($expectedData, $form->getData());
    }

    public function submitDataProvider(): array
    {
        $baseEmail = $this->getEntity(B2bCustomerEmail::class, ['email' => 'test@email.com']);
        $basePhone = $this->getEntity(B2bCustomerPhone::class, ['phone' => '12345678']);

        return [
            'new entity' => [
                new B2bCustomer(),
                [
                    'name' => 'name',
                    'contact' => 1,
                    'dataChannel' => '',
                    'shippingAddress' => ['label' => 'shipping address'],
                    'billingAddress' => ['label' => 'billing address'],
                ],
                (new B2bCustomer())
                    ->setName('name')
                    ->setContact($this->getEntity(Contact::class, ['first_name' => 'first name']))
                    ->setShippingAddress($this->getEntity(
                        Address::class,
                        ['label' => 'shipping address_stripped']
                    ))
                    ->setBillingAddress($this->getEntity(
                        Address::class,
                        ['label' => 'billing address_stripped']
                    ))
            ],
            'existing entity' => [
                (new B2bCustomer())
                    ->setName('name')
                    ->setContact($this->getEntity(Contact::class, ['first_name' => 'first name']))
                    ->addEmail($baseEmail)
                    ->addPhone($basePhone)
                    ->setShippingAddress($this->getEntity(Address::class, ['id' => '1', 'label' => 'shipping address']))
                    ->setBillingAddress($this->getEntity(Address::class, ['id' => '2', 'label' => 'billing address'])),
                [
                    'name' => 'name new',
                    'contact' => 2,
                    'emails' => [2],
                    'phones' => [2],
                    'shippingAddress' => ['id' => 1, 'label' => 'shipping new'],
                    'billingAddress' => ['id' => 2, 'label' => 'billing new'],
                ],
                (new B2bCustomer())
                    ->setName('name new')
                    ->setContact($this->getEntity(Contact::class, ['first_name' => 'first name new']))
                    ->addEmail($baseEmail)
                    ->addEmail($this->getEntity(B2bCustomerEmail::class, ['email' => 'test_new@email.com']))
                    ->removeEmail($baseEmail)
                    ->addPhone($basePhone)
                    ->addPhone($this->getEntity(B2bCustomerPhone::class, ['phone' => '87654321']))
                    ->removePhone($basePhone)
                    ->setShippingAddress($this->getEntity(
                        Address::class,
                        ['id' => '1', 'label' => 'shipping new_stripped']
                    ))
                    ->setBillingAddress($this->getEntity(
                        Address::class,
                        ['id' => '2', 'label' => 'billing new_stripped']
                    ))
            ]
        ];
    }
}
