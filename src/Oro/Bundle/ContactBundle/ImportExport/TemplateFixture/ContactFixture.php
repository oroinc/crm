<?php

namespace Oro\Bundle\ContactBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\ContactBundle\Entity\Method;
use Oro\Bundle\ContactBundle\Entity\Source;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;

/**
 * Fixture of Contact entity used for generation of import-export template
 */
class ContactFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'Oro\Bundle\ContactBundle\Entity\Contact';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('Jerry Coleman');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        return new Contact();
    }

    /**
     * @param string  $key
     * @param Contact $entity
     */
    public function fillEntityData($key, $entity)
    {
        $userRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\UserBundle\Entity\User');
        $accountRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\AccountBundle\Entity\Account');
        $contactAddressRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\ContactBundle\Entity\ContactAddress');
        $organizationRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\OrganizationBundle\Entity\Organization');

        switch ($key) {
            case 'Jerry Coleman':
                $primaryAddress = $contactAddressRepo->getEntity('Jerry Coleman');
                $entity
                    ->setId(1)
                    ->setNamePrefix('Mr.')
                    ->setFirstName('Jerry')
                    ->setLastName('Coleman')
                    ->setNameSuffix('Jr.')
                    ->setBirthday(new \DateTime('1973-03-07', new \DateTimeZone('UTC')))
                    ->setGender('male')
                    ->setDescription('Sample Contact')
                    ->setJobTitle('Manager')
                    ->setFax('713-450-0721')
                    ->setSkype('crm-jerrycoleman')
                    ->setTwitter('crm-jerrycoleman')
                    ->setFacebook('crm-jerrycoleman')
                    ->setGooglePlus('https://plus.google.com/454646545646546')
                    ->setLinkedIn('http://www.linkedin.com/in/crm-jerrycoleman')
                    ->setSource($this->createContactSource('website'))
                    ->setMethod($this->createContactMethod('phone'))
                    ->setOwner($userRepo->getEntity('John Doo'))
                    ->setOrganization($organizationRepo->getEntity('default'))
                    ->setAssignedTo($userRepo->getEntity('John Doo'))
                    ->addEmail($this->createContactEmail('JerryAColeman@armyspy.com', true))
                    ->addEmail($this->createContactEmail('JerryAColeman@cuvox.de'))
                    ->addEmail($this->createContactEmail('JerryAColeman@teleworm.us'))
                    ->addPhone($this->createContactPhone('585-255-1127', true))
                    ->addPhone($this->createContactPhone('914-412-0298'))
                    ->addPhone($this->createContactPhone('310-430-7876'))
                    ->addGroup($this->createContactGroup('Sales Group'))
                    ->addGroup($this->createContactGroup('Marketing Group'))
                    ->addAccount($accountRepo->getEntity('Coleman'))
                    ->addAccount($accountRepo->getEntity('Smith'))
                    ->addAddress($primaryAddress)
                    ->addAddress($this->createContactAddress('Jerry Coleman', 2))
                    ->addAddress($this->createContactAddress('Jerry Coleman', 3))
                    ->setPrimaryAddress($primaryAddress);
                return;
            case 'John Smith':
                $primaryAddress = $this->createContactAddress('John Smith', 1);
                $entity
                    ->setId(2)
                    ->setNamePrefix('Mr.')
                    ->setFirstName('John')
                    ->setLastName('Smith')
                    ->setNameSuffix('Jr.')
                    ->setBirthday(new \DateTime('1973-03-07', new \DateTimeZone('UTC')))
                    ->setGender('male')
                    ->setDescription('Sample Contact')
                    ->setJobTitle('Manager')
                    ->setFax('713-450-0721')
                    ->setSkype('crm-johnsmith')
                    ->setTwitter('crm-johnsmith')
                    ->setFacebook('crm-johnsmith')
                    ->setGooglePlus('https://plus.google.com/343535434535435')
                    ->setLinkedIn('http://www.linkedin.com/in/crm-johnsmith')
                    ->setSource($this->createContactSource('website'))
                    ->setMethod($this->createContactMethod('phone'))
                    ->setOwner($userRepo->getEntity('John Doo'))
                    ->setOrganization($organizationRepo->getEntity('default'))
                    ->setAssignedTo($userRepo->getEntity('John Doo'))
                    ->addEmail($this->createContactEmail('JohnSmith@armyspy.com', true))
                    ->addEmail($this->createContactEmail('JohnSmith@cuvox.de'))
                    ->addEmail($this->createContactEmail('JohnSmith@teleworm.us'))
                    ->addPhone($this->createContactPhone('585-255-1127', true))
                    ->addPhone($this->createContactPhone('914-412-0298'))
                    ->addPhone($this->createContactPhone('310-430-7876'))
                    ->addGroup($this->createContactGroup('Sales Group'))
                    ->addGroup($this->createContactGroup('Marketing Group'))
                    ->addAccount($accountRepo->getEntity('Smith'))
                    ->addAccount($accountRepo->getEntity('Coleman'))
                    ->addAddress($primaryAddress)
                    ->addAddress($this->createContactAddress('John Smith', 2))
                    ->addAddress($this->createContactAddress('John Smith', 3))
                    ->setPrimaryAddress($primaryAddress);
                return;
        }

        parent::fillEntityData($key, $entity);
    }

    /**
     * @param string $email
     * @param bool   $primary
     *
     * @return ContactEmail
     */
    protected function createContactEmail($email, $primary = false)
    {
        $entity = new ContactEmail($email);
        if ($primary) {
            $entity->setPrimary(true);
        }

        return $entity;
    }

    /**
     * @param string $phone
     * @param bool   $primary
     *
     * @return ContactPhone
     */
    protected function createContactPhone($phone, $primary = false)
    {
        $entity = new ContactPhone($phone);
        if ($primary) {
            $entity->setPrimary(true);
        }

        return $entity;
    }

    /**
     * @param string $name
     *
     * @return Group
     */
    protected function createContactGroup($name)
    {
        return new Group($name);
    }

    /**
     * @param string $name
     *
     * @return Method
     */
    protected function createContactMethod($name)
    {
        return new Method($name);
    }

    /**
     * @param string $name
     *
     * @return Source
     */
    protected function createContactSource($name)
    {
        return new Source($name);
    }

    /**
     * @param string $name
     * @param int    $number
     *
     * @return ContactAddress
     *
     * @throws \LogicException
     */
    protected function createContactAddress($name, $number)
    {
        $countryRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\AddressBundle\Entity\Country');
        $regionRepo  = $this->templateManager
            ->getEntityRepository('Oro\Bundle\AddressBundle\Entity\Region');

        $entity = new ContactAddress();

        switch ($name) {
            case 'Jerry Coleman':
                $entity
                    ->setFirstName('Jerry')
                    ->setLastName('Coleman');
                break;
            case 'John Smith':
                $entity
                    ->setFirstName('John')
                    ->setLastName('Smith');
                break;
            default:
                throw new \LogicException(
                    sprintf(
                        'Undefined contact address. Name: %s. Number: %d.',
                        $name,
                        $number
                    )
                );
        }

        switch ($number) {
            case 1:
                $entity
                    ->setCity('Rochester')
                    ->setStreet('1215 Caldwell Road')
                    ->setPostalCode('14608')
                    ->setRegion($regionRepo->getEntity('NY'))
                    ->setCountry($countryRepo->getEntity('US'));
                break;
            case 2:
                $entity
                    ->setCity('New York')
                    ->setStreet('4677 Pallet Street')
                    ->setPostalCode('10011')
                    ->setRegion($regionRepo->getEntity('NY'))
                    ->setCountry($countryRepo->getEntity('US'));
                break;
            case 3:
                $entity
                    ->setCity('New York')
                    ->setStreet('52 Jarvisville Road')
                    ->setPostalCode('11590')
                    ->setRegion($regionRepo->getEntity('NY'))
                    ->setCountry($countryRepo->getEntity('US'));
                break;
            default:
                throw new \LogicException(
                    sprintf(
                        'Undefined contact address. Name: %s. Number: %d.',
                        $name,
                        $number
                    )
                );
        }

        return $entity;
    }
}
