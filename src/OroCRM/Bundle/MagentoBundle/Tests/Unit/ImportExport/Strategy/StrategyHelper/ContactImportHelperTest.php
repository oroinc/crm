<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Strategy\StrategyHelper;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\ContactImportHelper;

class ContactImportHelperTest extends \PHPUnit_Framework_TestCase
{
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

    protected function getHelper(Channel $channel)
    {
        $helper = new ContactImportHelper($channel, $this->addressImportHelper);

        return $helper;
    }
}
