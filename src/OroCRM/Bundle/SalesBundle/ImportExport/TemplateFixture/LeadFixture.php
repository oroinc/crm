<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\TemplateFixture;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadStatus;

class LeadFixture implements TemplateFixtureInterface
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
     * @var TemplateFixtureInterface
     */
    protected $accountFixture;

    /**
     * @param TemplateFixtureInterface $userFixture
     * @param TemplateFixtureInterface $contactFixture
     * @param TemplateFixtureInterface $accountFixture
     */
    public function __construct(
        TemplateFixtureInterface $userFixture,
        TemplateFixtureInterface $contactFixture,
        TemplateFixtureInterface $accountFixture
    ) {
        $this->userFixture = $userFixture;
        $this->contactFixture = $contactFixture;
        $this->accountFixture = $accountFixture;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $user = $this->userFixture->getData()->current();
        $contact = $this->contactFixture->getData()->current();
        $account = $this->accountFixture->getData()->current();

        $region = new Region('US-NY');
        $region->setCode('NY');

        $country = new Country('US');

        $address = new Address();
        $address->setCity('Rochester')
            ->setStreet('1215 Caldwell Road')
            ->setPostalCode('14608')
            ->setFirstName('Jerry')
            ->setLastName('Coleman')
            ->setRegion($region)
            ->setCountry($country);

        $lead = new Lead();
        $lead->setName('Oro Inc. Lead Name')
            ->setCompanyName('Oro Inc.')
            ->setOwner($user)
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
            ->setAccount($account)
            ->setContact($contact)
            ->setAddress($address)
            ->setEmail('JerryAColeman@armyspy.com')
            ->setNamePrefix('Mr.')
            ->setFirstName('Jerry')
            ->setLastName('Coleman')
            ->setNameSuffix('Jr.')
            ->setStatus(new LeadStatus('New'))
            ->setJobTitle('Manager')
            ->setPhoneNumber('585-255-1127')
            ->setWebsite('http://orocrm.com')
            ->setNumberOfEmployees(100)
            ->setIndustry('Internet');

        return new \ArrayIterator(array($lead));
    }
}