<?php

namespace OroCRM\Bundle\AccountBundle\ImportExport\TemplateFixture;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use OroCRM\Bundle\AccountBundle\Entity\Account;

class AccountFixture implements TemplateFixtureInterface
{
    /**
     * @var TemplateFixtureInterface
     */
    protected $userFixture;

    /**
     * @var TemplateFixtureInterface
     */
    protected $contactFixture;

    /**
     * @param TemplateFixtureInterface $userFixture
     * @param TemplateFixtureInterface $contactFixture
     */
    public function __construct(TemplateFixtureInterface $userFixture, TemplateFixtureInterface $contactFixture)
    {
        $this->userFixture = $userFixture;
        $this->contactFixture = $contactFixture;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $user = $this->userFixture->getData()->current();
        $contact = $this->contactFixture->getData()->current();

        $region = new Region('US-NY');
        $region->setCode('NY');

        $country = new Country('US');

        $billingAddress = new Address();
        $billingAddress->setCity('Rochester')
            ->setStreet('1215 Caldwell Road')
            ->setPostalCode('14608')
            ->setFirstName('Jerry')
            ->setLastName('Coleman')
            ->setRegion($region)
            ->setCountry($country);

        $shippingAddress = new Address();
        $shippingAddress->setCity('New York')
            ->setStreet('4677 Pallet Street')
            ->setPostalCode('10011')
            ->setFirstName('Jerry')
            ->setLastName('Coleman')
            ->setRegion($region)
            ->setCountry($country);

        $account = new Account();
        $account->setId(1)
            ->setName('Oro Inc.')
            ->setOwner($user)
            ->setBillingAddress($shippingAddress)
            ->setShippingAddress($shippingAddress)
            ->addContact($contact)
            ->setDefaultContact($contact);

        return new \ArrayIterator(array($account));
    }
}
