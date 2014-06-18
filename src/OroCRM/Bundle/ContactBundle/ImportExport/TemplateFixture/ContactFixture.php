<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\TemplateFixture;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\ContactBundle\Entity\Method;
use OroCRM\Bundle\ContactBundle\Entity\Source;

class ContactFixture implements TemplateFixtureInterface
{
    /**
     * @var TemplateFixtureInterface
     */
    protected $userFixture;

    /**
     * @param TemplateFixtureInterface $userFixture
     */
    public function __construct(TemplateFixtureInterface $userFixture)
    {
        $this->userFixture = $userFixture;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $user = $this->userFixture->getData()->current();

        $primaryEmail = new ContactEmail('JerryAColeman@armyspy.com');
        $primaryEmail->setPrimary(true);

        $primaryPhone = new ContactPhone('585-255-1127');
        $primaryPhone->setPrimary(true);

        $accountOne = new Account();
        $accountOne->setName('Welsight73A_Coleman');

        $accountTwo = new Account();
        $accountTwo->setName('DistaildD_Coleman');

        $region = new Region('US-NY');
        $region->setCode('NY');

        $country = new Country('US');

        $primaryAddress = new ContactAddress();
        $primaryAddress->setCity('Rochester')
            ->setStreet('1215 Caldwell Road')
            ->setPostalCode('14608')
            ->setFirstName('Jerry')
            ->setLastName('Coleman')
            ->setRegion($region)
            ->setCountry($country);

        $addressOne = new ContactAddress();
        $addressOne->setCity('New York')
            ->setStreet('4677 Pallet Street')
            ->setPostalCode('10011')
            ->setFirstName('Jerry')
            ->setLastName('Coleman')
            ->setRegion($region)
            ->setCountry($country);

        $addressTwo = new ContactAddress();
        $addressTwo->setCity('Westbury')
            ->setStreet('52 Jarvisville Road')
            ->setPostalCode('11590')
            ->setFirstName('Jerry')
            ->setLastName('Coleman')
            ->setRegion($region)
            ->setCountry($country);

        $contact = new Contact();
        $contact
            ->setId(1)
            ->setNamePrefix('Mr.')
            ->setFirstName('Jerry')
            ->setLastName('Coleman')
            ->setNameSuffix('Jr.')
            ->setBirthday(new \DateTime('1973-03-07'))
            ->setGender('male')
            ->setDescription('Sample Contact')
            ->setJobTitle('Manager')
            ->setFax('713-450-0721')
            ->setSkype('crm-jerrycoleman')
            ->setTwitter('crm-jerrycoleman')
            ->setFacebook('crm-jerrycoleman')
            ->setGooglePlus('https://plus.google.com/454646545646546')
            ->setLinkedIn('http://www.linkedin.com/in/crm-jerrycoleman')
            ->setSource(new Source('website'))
            ->setMethod(new Method('phone'))
            ->setOwner($user)
            ->setAssignedTo($user)
            ->addEmail($primaryEmail)
            ->addEmail(new ContactEmail('JerryAColeman@cuvox.de'))
            ->addEmail(new ContactEmail('JerryAColeman@teleworm.us'))
            ->addPhone($primaryPhone)
            ->addPhone(new ContactPhone('914-412-0298'))
            ->addPhone(new ContactPhone('310-430-7876'))
            ->addGroup(new Group('Sales Group'))
            ->addGroup(new Group('Marketing Group'))
            ->addAccount($accountOne)
            ->addAccount($accountTwo)
            ->addAddress($primaryAddress)
            ->addAddress($addressOne)
            ->addAddress($addressTwo)
            ->setPrimaryAddress($primaryAddress);

        return new \ArrayIterator(array($contact));
    }
}
