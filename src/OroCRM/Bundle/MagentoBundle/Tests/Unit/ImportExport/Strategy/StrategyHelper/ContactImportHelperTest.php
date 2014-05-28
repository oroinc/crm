<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Strategy\StrategyHelper;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Form\EventListener\ChannelFormTwoWaySyncSubscriber;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\ContactImportHelper;

class ContactImportHelperTest extends \PHPUnit_Framework_TestCase
{
    const TEST_CONTACT_ADDRESS_ID = 123;

    /** @var AddressImportHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressImportHelper;

    public function setUp()
    {
        $this->addressImportHelper = $this
            ->getMockBuilder('OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper')
            ->disableOriginalConstructor()->getMock();
    }

    public function tearDown()
    {
        unset($this->addressImportHelper);
    }

    public function testAddressCreation()
    {
        $channel        = new Channel();
        $contact        = new Contact();
        $localCustomer  = new Customer();
        $remoteCustomer = new Customer();

        $address = new Address();
        $address->setCountry(new Country('US'));
        $remoteCustomer->addAddress($address);

        $helper = $this->getHelper($channel);
        $helper->merge($remoteCustomer, $localCustomer, $contact);

        $this->assertCount(1, $contact->getAddresses());
    }

    /**
     * @dataProvider addressTypesUpdateDataProvider
     *
     * @param string          $priority
     * @param ArrayCollection $remoteTypes
     * @param ArrayCollection $localTypes
     * @param ArrayCollection $contactTypes
     * @param array           $expectedTypeNames
     */
    public function testAddressTypesUpdate(
        $priority,
        ArrayCollection $remoteTypes,
        ArrayCollection $localTypes,
        ArrayCollection $contactTypes,
        array $expectedTypeNames
    ) {
        $channel = new Channel();
        $channel->setSyncPriority($priority);

        $testCountry = new Country('US');

        $contact        = new Contact();
        $contactAddress = new ContactAddress();
        $contactAddress->setId(self::TEST_CONTACT_ADDRESS_ID);
        $contactAddress->setTypes($contactTypes);
        $contactAddress->setCountry($testCountry);
        $contact->addAddress($contactAddress);

        $localCustomer = new Customer();
        $localAddress  = new Address();
        $localAddress->setContactAddress($contactAddress);
        $localAddress->setTypes($localTypes);
        $localAddress->setCountry($testCountry);
        $localCustomer->addAddress($localAddress);

        $remoteCustomer = new Customer();
        $remoteAddress  = new Address();
        $remoteAddress->setContactAddress($contactAddress);
        $remoteAddress->setTypes($remoteTypes);
        $remoteAddress->setCountry($testCountry);
        $remoteCustomer->addAddress($remoteAddress);

        $helper = $this->getHelper($channel);
        $helper->merge($remoteCustomer, $localCustomer, $contact);

        $this->assertCount(1, $contact->getAddresses());
        $this->assertEquals($expectedTypeNames, $contactAddress->getTypeNames());
    }

    /**
     * @return array
     */
    public function addressTypesUpdateDataProvider()
    {
        $billingType  = new AddressType('billing');
        $shippingType = new AddressType('shipping');

        return [
            'remote wins use remote types even if changed locally'   => [
                ChannelFormTwoWaySyncSubscriber::REMOTE_WINS,
                new ArrayCollection([$billingType]),
                new ArrayCollection([]),
                new ArrayCollection([$shippingType]),
                ['billing']
            ],
            'local wins use local types even if changed remote'      => [
                ChannelFormTwoWaySyncSubscriber::LOCAL_WINS,
                new ArrayCollection([$billingType]),
                new ArrayCollection([]),
                new ArrayCollection([$shippingType]),
                ['shipping']
            ],
            'should update type even if local wins but no conflicts' => [
                ChannelFormTwoWaySyncSubscriber::LOCAL_WINS,
                new ArrayCollection([$billingType]),
                new ArrayCollection([$shippingType]),
                new ArrayCollection([$shippingType]),
                ['billing']
            ]
        ];
    }

    /**
     * @param Channel $channel
     *
     * @return ContactImportHelper
     */
    protected function getHelper(Channel $channel)
    {
        $helper = new ContactImportHelper($channel, $this->addressImportHelper);

        return $helper;
    }
}
