<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\AddressBundle\Form\EventListener\AddressCountryAndRegionSubscriber;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;
use Oro\Bundle\FormBundle\Form\Extension\AdditionalAttrExtension;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail;
use Oro\Bundle\SalesBundle\Entity\B2bCustomerPhone;
use Oro\Bundle\SalesBundle\Form\Type\B2bCustomerType;
use Oro\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub\AddressTypeStub;
use Oro\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub\EmailCollectionTypeStub;
use Oro\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub\PhoneCollectionTypeStub;

use Oro\Component\Testing\Unit\EntityTrait;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;

class B2bCustomerTypeTest extends FormIntegrationTestCase
{
    use EntityTrait;

    /** @var B2bCustomerType */
    private $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->formType = new B2bCustomerType(new PropertyAccessor());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $this->formType = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $contactEntityType = new EntityType([
            1 => $this->getEntity(Contact::class, ['first_name' => 'first name']),
            2 => $this->getEntity(Contact::class, ['first_name' => 'first name new']),
        ], 'oro_contact_select');
        $channelEntityType = new EntityType([], 'genemu_jqueryselect2_entity');
        $emailEntityType = new EntityType([
            1 => $this->getEntity(B2bCustomerEmail::class, ['email' => 'test@email.com']),
            2 => $this->getEntity(B2bCustomerEmail::class, ['email' => 'test_new@email.com']),
        ], 'test_email_entity');
        $phoneEntityType = new EntityType([
            1 => $this->getEntity(B2bCustomerPhone::class, ['phone' => '12345678']),
            2 => $this->getEntity(B2bCustomerPhone::class, ['phone' => '87654321'])
        ], 'test_phone_entity');
        $countryEntityType = new EntityType([], 'oro_country');
        $regionEntityType = new EntityType([], 'oro_region');

        $channelsProvider = $this->createMock(ChannelsByEntitiesProvider::class);
        $repository = $this->createMock(ObjectRepository::class);
        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        return [
            new PreloadedExtension(
                [
                    $contactEntityType->getName() => $contactEntityType,
                    'oro_email_collection' => new EmailCollectionTypeStub(),
                    $emailEntityType->getName() => $emailEntityType,
                    'oro_phone_collection' => new PhoneCollectionTypeStub(),
                    $phoneEntityType->getName() => $phoneEntityType,
                    ChannelSelectType::NAME => new ChannelSelectType($channelsProvider),
                    $channelEntityType->getName() => $channelEntityType,
                    'oro_address' => new AddressTypeStub(
                        new AddressCountryAndRegionSubscriber($objectManager, $formFactory)
                    ),
                    $countryEntityType->getName() => $countryEntityType,
                    $regionEntityType->getName() => $regionEntityType,
                ],
                [
                    'form' => [new AdditionalAttrExtension()]
                ]
            )
        ];
    }

    public function testGetName()
    {
        $this->assertEquals('oro_sales_b2bcustomer', $this->formType->getName());
    }

    public function testGetBlockPrefix()
    {
        $this->assertEquals('oro_sales_b2bcustomer', $this->formType->getBlockPrefix());
    }

    public function testBuildForm()
    {
        $form = $this->factory->create($this->formType);

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
     *
     * @param B2bCustomer $existingData
     * @param array       $submittedData
     * @param B2bCustomer $expectedData
     */
    public function testSubmit($existingData, $submittedData, $expectedData)
    {
        $form = $this->factory->create($this->formType, $existingData);

        $this->assertEquals($existingData, $form->getData());

        $form->submit($submittedData);
        $this->assertTrue($form->isValid());

        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @return array
     */
    public function submitDataProvider()
    {
        $baseEmail = $this->getEntity(B2bCustomerEmail::class, ['email' => 'test@email.com']);
        $basePhone = $this->getEntity(B2bCustomerPhone::class, ['phone' => '12345678']);

        return [
            'new entity' => [
                new B2bCustomer(),
                [
                    'name' => 'name',
                    'contact' => 1,
                    'dataChannel' => [],
                    'shippingAddress' => ['id' => 1, 'label' => 'shipping address'],
                    'billingAddress' => ['id' => 2, 'label' => 'billing address'],
                ],
                (new B2bCustomer())
                    ->setName('name')
                    ->setContact($this->getEntity(Contact::class, ['first_name' => 'first name']))
                    ->setShippingAddress($this->getEntity(Address::class, ['id' => '1', 'label' => 'shipping address']))
                    ->setBillingAddress($this->getEntity(Address::class, ['id' => '2', 'label' => 'billing address']))
            ],
            'existing entity' => [
                (new B2bCustomer())
                    ->setName('name')
                    ->setContact($this->getEntity(Contact::class, ['first_name' => 'first name']))
                    ->addEmail($baseEmail)
                    ->addPhone($basePhone)
                    ->setShippingAddress($this->getEntity(Address::class, ['id' => 1, 'label' => 'shipping address']))
                    ->setBillingAddress($this->getEntity(Address::class, ['id' => 2, 'label' => 'billing address'])),
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
                    ->setShippingAddress($this->getEntity(Address::class, ['id' => 1, 'label' => 'shipping new']))
                    ->setBillingAddress($this->getEntity(Address::class, ['id' => 2, 'label' => 'billing new']))
            ]
        ];
    }
}
