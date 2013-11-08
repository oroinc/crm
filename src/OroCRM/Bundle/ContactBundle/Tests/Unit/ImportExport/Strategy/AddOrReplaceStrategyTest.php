<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use OroCRM\Bundle\ContactBundle\ImportExport\Strategy\Import\AddOrReplaceStrategy;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\ContactBundle\Entity\Source;
use OroCRM\Bundle\ContactBundle\Entity\Method;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;

class AddOrReplaceStrategyTest extends \PHPUnit_Framework_TestCase
{
    const CURRENT_USER_ID = 1;
    const CURRENT_CONTACT_ID = 2;
    const CURRENT_GROUP_ID = 3;
    const CURRENT_ACCOUNT_ID = 4;

    /**
     * @param Contact $sourceContact
     * @param Contact $expectedContact
     * @dataProvider processDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testProcess(Contact $sourceContact, Contact $expectedContact)
    {
        $strategyHelper = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper')
            ->disableOriginalConstructor()
            ->setMethods(array('importEntity', 'validateEntity'))
            ->getMock();
        $strategyHelper->expects($this->any())
            ->method('importEntity')
            ->with(
                $this->isInstanceOf('OroCRM\Bundle\ContactBundle\Entity\Contact'),
                $this->isInstanceOf('OroCRM\Bundle\ContactBundle\Entity\Contact'),
                array()
            )
            ->will(
                $this->returnCallback(
                    function (Contact $basicContact, Contact $importedContact) {
                        $basicContact->resetEmails($importedContact->getEmails());
                        $basicContact->resetPhones($importedContact->getPhones());
                        $basicContact->resetAddresses($importedContact->getAddresses());
                    }
                )
            );

        $contactStrategyHelper = $this->getMockBuilder(
            'OroCRM\Bundle\ContactBundle\ImportExport\Strategy\Import\ContactImportStrategyHelper'
        )
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getContactOrNull',
                    'getSecurityContextUserOrNull',
                    'getSourceOrNull',
                    'getMethodOrNull',
                    'getUserOrNull',
                    'getCountryOrNull',
                    'getRegionOrNull',
                    'getAddressTypeOrNull',
                    'getGroupOrNull',
                    'getAccountOrNull',
                )
            )
            ->getMock();
        $contactStrategyHelper->expects($this->once())
            ->method('getContactOrNull')
            ->with($this->isInstanceOf('OroCRM\Bundle\ContactBundle\Entity\Contact'))
            ->will($this->returnCallback(array($this, 'getContactOrNull')));
        $contactStrategyHelper->expects($this->any())
            ->method('getSecurityContextUserOrNull')
            ->will($this->returnValue($this->getCurrentUser()));
        $contactStrategyHelper->expects($this->any())
            ->method('getSourceOrNull')
            ->with($this->isInstanceOf('OroCRM\Bundle\ContactBundle\Entity\Source'))
            ->will($this->returnCallback(array($this, 'getSourceOrNull')));
        $contactStrategyHelper->expects($this->any())
            ->method('getMethodOrNull')
            ->with($this->isInstanceOf('OroCRM\Bundle\ContactBundle\Entity\Method'))
            ->will($this->returnCallback(array($this, 'getMethodOrNull')));
        $contactStrategyHelper->expects($this->any())
            ->method('getUserOrNull')
            ->with($this->isInstanceOf('Oro\Bundle\UserBundle\Entity\User'))
            ->will($this->returnCallback(array($this, 'getUserOrNull')));
        $contactStrategyHelper->expects($this->any())
            ->method('getCountryOrNull')
            ->with($this->isInstanceOf('Oro\Bundle\AddressBundle\Entity\Country'))
            ->will($this->returnCallback(array($this, 'getCountryOrNull')));
        $contactStrategyHelper->expects($this->any())
            ->method('getRegionOrNull')
            ->with($this->isInstanceOf('Oro\Bundle\AddressBundle\Entity\Region'))
            ->will($this->returnCallback(array($this, 'getRegionOrNull')));
        $contactStrategyHelper->expects($this->any())
            ->method('getAddressTypeOrNull')
            ->with($this->isInstanceOf('Oro\Bundle\AddressBundle\Entity\AddressType'))
            ->will($this->returnCallback(array($this, 'getAddressTypeOrNull')));
        $contactStrategyHelper->expects($this->any())
            ->method('getGroupOrNull')
            ->with($this->isInstanceOf('OroCRM\Bundle\ContactBundle\Entity\Group'))
            ->will($this->returnCallback(array($this, 'getGroupOrNull')));
        $contactStrategyHelper->expects($this->any())
            ->method('getAccountOrNull')
            ->with($this->isInstanceOf('OroCRM\Bundle\AccountBundle\Entity\Account'))
            ->will($this->returnCallback(array($this, 'getAccountOrNull')));

        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        if ($expectedContact->getId()) {
            $context->expects($this->once())->method('incrementReplaceCount');
        } else {
            $context->expects($this->once())->method('incrementAddCount');
        }

        $strategy = new AddOrReplaceStrategy($strategyHelper, $contactStrategyHelper);
        $strategy->setImportExportContext($context);
        $actualContact = $strategy->process($sourceContact);

        if ($actualContact->getId()) {
            $this->assertEquals($expectedContact->getId(), $actualContact->getId());
            $this->assertEquals($expectedContact->getSource(), $actualContact->getSource());
            $this->assertEquals($expectedContact->getMethod(), $actualContact->getMethod());
            $this->assertEquals($expectedContact->getOwner(), $actualContact->getOwner());
            $this->assertEquals($expectedContact->getAssignedTo(), $actualContact->getAssignedTo());
            $this->assertEquals($expectedContact->getGroups()->getValues(), $actualContact->getGroups()->getValues());

            $this->assertSameSize($expectedContact->getAddresses(), $actualContact->getAddresses());
            $expectedAddresses = $expectedContact->getAddresses()->getValues();
            $actualAddresses = $actualContact->getAddresses()->getValues();
            foreach ($expectedAddresses as $key => $expectedAddress) {
                /** @var ContactAddress $expectedAddress */
                /** @var ContactAddress $actualAddress */
                $actualAddress = $actualAddresses[$key];
                $this->assertEquals($expectedAddress->getCountry(), $actualAddress->getCountry());
                $this->assertEquals($expectedAddress->getRegion(), $actualAddress->getRegion());
                $this->assertEquals($expectedAddress->getTypes()->getValues(), $actualAddress->getTypes()->getValues());
            }

            $this->assertEquals(
                $expectedContact->getAccounts()->getValues(),
                $actualContact->getAccounts()->getValues()
            );

            $this->assertSameSize($expectedContact->getEmails(), $actualContact->getEmails());
            $expectedEmails = $expectedContact->getEmails();
            $actualEmails = $actualContact->getEmails();
            foreach ($expectedEmails as $key => $expectedEmail) {
                /** @var ContactEmail $expectedEmail */
                /** @var ContactEmail $actualEmail */
                $actualEmail = $actualEmails[$key];
                $this->assertEquals($expectedEmail->getEmail(), $actualEmail->getEmail());
                $this->assertEquals($expectedEmail->getOwner()->getId(), $actualEmail->getOwner()->getId());
            }

            $this->assertSameSize($expectedContact->getPhones(), $actualContact->getPhones());
            $expectedPhones = $expectedContact->getPhones();
            $actualPhones = $actualContact->getPhones();
            foreach ($expectedPhones as $key => $expectedPhone) {
                /** @var ContactPhone $expectedPhone */
                /** @var ContactPhone $actualPhone */
                $actualPhone = $actualPhones[$key];
                $this->assertEquals($expectedPhone->getPhone(), $actualPhone->getPhone());
                $this->assertEquals($expectedPhone->getOwner()->getId(), $actualPhone->getOwner()->getId());
            }

            $this->assertNotEmpty($actualContact->getCreatedAt());
            $this->assertNotEmpty($actualContact->getUpdatedAt());
            $this->assertEquals($expectedContact->getCreatedBy()->getId(), $actualContact->getCreatedBy()->getId());
            $this->assertEquals($expectedContact->getUpdatedBy()->getId(), $actualContact->getUpdatedBy()->getId());
        } else {
            $this->assertEquals($expectedContact, $actualContact);
        }
    }

    /**
     * @param Contact $contact
     * @return null|Contact
     */
    public function getContactOrNull(Contact $contact)
    {
        if ($contact->getId()) {
            $existingContact = clone $contact;
            $existingContact->resetEmails($contact->getEmails());
            $existingContact->resetPhones($contact->getPhones());
            $existingContact->resetAddresses($contact->getAddresses());

            return $existingContact;
        }

        return  null;
    }

    /**
     * @param Source $source
     * @return null|Source
     */
    public function getSourceOrNull(Source $source)
    {
        if ($source->getName()) {
            $existingSource = clone $source;
            $existingSource->setLabel($source->getName());
            return $existingSource;
        }
        return null;
    }

    /**
     * @param Method $method
     * @return null|Method
     */
    public function getMethodOrNull(Method $method)
    {
        if ($method->getName()) {
            $existingMethod = clone $method;
            $existingMethod->setLabel($method->getName());
            return $existingMethod;
        }
        return null;
    }

    /**
     * @param User $user
     * @return null|User
     */
    public function getUserOrNull(User $user)
    {
        if ($user->getFirstName() && $user->getLastName()) {
            $existingUser = clone $user;
            $existingUser->setUsername($user->getFirstName() . $user->getLastName());
            return $existingUser;
        }
        return null;
    }

    /**
     * @param Country $country
     * @return null|Country
     */
    public function getCountryOrNull(Country $country)
    {
        if ($country->getIso2Code()) {
            $existingCountry = clone $country;
            $existingCountry->setName($country->getIso2Code());
            return $existingCountry;
        }
        return null;
    }

    /**
     * @param Region $region
     * @return null|Region
     */
    public function getRegionOrNull(Region $region)
    {
        if ($region->getCombinedCode()) {
            $existingRegion = clone $region;
            $existingRegion->setName($region->getCombinedCode());
            return $existingRegion;
        }
        return null;
    }

    /**
     * @param AddressType $addressType
     * @return null|AddressType
     */
    public function getAddressTypeOrNull(AddressType $addressType)
    {
        if ($addressType->getName()) {
            $existingType = clone $addressType;
            $existingType->setLabel($addressType->getName());
            return $existingType;
        }
        return null;
    }

    /**
     * @param Group $group
     * @return null|Group
     */
    public function getGroupOrNull(Group $group)
    {
        if ($group->getLabel()) {
            $existingGroup = clone $group;
            $groupIdReflection = $this->getGroupIdReflection();
            $groupIdReflection->setValue($existingGroup, AddOrReplaceStrategyTest::CURRENT_GROUP_ID);
            return $existingGroup;
        }
        return null;
    }

    /**
     * @param Account $account
     * @return null|Account
     */
    public function getAccountOrNull(Account $account)
    {
        if ($account->getName()) {
            $existingAccount = clone $account;
            $existingAccount->setId(AddOrReplaceStrategyTest::CURRENT_ACCOUNT_ID);
            return $existingAccount;
        }
        return null;
    }

    /**
     * @return \ReflectionProperty
     */
    protected function getGroupIdReflection()
    {
        $groupIdReflection = new \ReflectionProperty('OroCRM\Bundle\ContactBundle\Entity\Group', 'id');
        $groupIdReflection->setAccessible(true);

        return $groupIdReflection;
    }

    /**
     * @return User
     */
    protected function getCurrentUser()
    {
        $currentUser = new User();
        $currentUser->setId(self::CURRENT_USER_ID);

        return $currentUser;
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function processDataProvider()
    {
        $minimalNotExistingContact = new Contact();
        $minimalNotExistingContact
            ->setFirstName('first')
            ->setLastName('last');

        $owner = new User();
        $owner->setFirstName('owner first')
            ->setLastName('owner last');
        $assignedTo = new User();
        $assignedTo->setFirstName('assignedTo first')
            ->setLastName('assignedTo last');
        $firstAddress = new ContactAddress();
        $firstAddress->addType(new AddressType(AddressType::TYPE_BILLING))
            ->setCountry(new Country('US'))
            ->setRegion(new Region('US.AL'))
            ->setStreet('second street')
            ->setCity('second city')
            ->setPostalCode('SECOND');
        $secondAddress = new ContactAddress();
        $secondAddress->addType(new AddressType(AddressType::TYPE_SHIPPING))
            ->setCountry(new Country('US'))
            ->setRegion(new Region('US.FL'))
            ->setStreet('first street')
            ->setCity('first city')
            ->setPostalCode('FIRST');
        $firstAccount = new Account();
        $firstAccount->setName('first account');
        $secondAccount = new Account();
        $secondAccount->setName('second account');
        $firstEmail = new ContactEmail();
        $firstEmail->setEmail('first@qqwe.com');
        $secondEmail = new ContactEmail();
        $secondEmail->setEmail('second@qqwe.com');
        $firstPhone = new ContactPhone();
        $firstPhone->setPhone('1111111111');
        $secondPhone = new ContactPhone();
        $secondPhone->setPhone('2222222222');

        $fullExistingContact = new Contact();
        $fullExistingContact
            ->setId(self::CURRENT_CONTACT_ID)
            ->setSource(new Source('tv'))
            ->setMethod(new Method('email'))
            ->setOwner($owner)
            ->setAssignedTo($assignedTo)
            ->addGroup(new Group('first group'))
            ->addGroup(new Group('second group'))
            ->addAddress($firstAddress)
            ->addAddress($secondAddress)
            ->addAccount($firstAccount)
            ->addAccount($secondAccount)
            ->addEmail($firstEmail)
            ->addEmail($secondEmail)
            ->addPhone($firstPhone)
            ->addPhone($secondPhone);

        $expectedFullExistingContact = new Contact();
        $expectedFullExistingContact
            ->setId(self::CURRENT_CONTACT_ID)
            ->setSource($this->getSourceOrNull($fullExistingContact->getSource()))
            ->setMethod($this->getMethodOrNull($fullExistingContact->getMethod()))
            ->setOwner($this->getUserOrNull($fullExistingContact->getOwner()))
            ->setAssignedTo($this->getUserOrNull($fullExistingContact->getAssignedTo()));
        foreach ($fullExistingContact->getGroups() as $group) {
            $expectedFullExistingContact->addGroup($this->getGroupOrNull($group));
        }
        foreach ($fullExistingContact->getAddresses() as $address) {
            $existingAddress = new ContactAddress();
            $existingAddress->setStreet($address->getStreet());
            $existingAddress->setCity($address->getCity());
            $existingAddress->setPostalCode($address->getPostalCode());
            $existingAddress->setCountry($this->getCountryOrNull($address->getCountry()));
            $existingAddress->setRegion($this->getRegionOrNull($address->getRegion()));
            foreach ($address->getTypes() as $type) {
                $existingAddress->addType($this->getAddressTypeOrNull($type));
            }
            $expectedFullExistingContact->addAddress($existingAddress);
        }
        foreach ($fullExistingContact->getAccounts() as $account) {
            $expectedFullExistingContact->addAccount($this->getAccountOrNull($account));
        }
        foreach ($fullExistingContact->getEmails() as $email) {
            $expectedEmail = clone $email;
            $expectedEmail->setOwner($expectedFullExistingContact);
            $expectedFullExistingContact->addEmail($expectedEmail);
        }
        foreach ($fullExistingContact->getPhones() as $phone) {
            $expectedPhone = clone $phone;
            $expectedPhone->setOwner($expectedFullExistingContact);
            $expectedFullExistingContact->addPhone($expectedPhone);
        }

        $currentUser = $this->getCurrentUser();
        $expectedFullExistingContact->setCreatedBy($currentUser);
        $expectedFullExistingContact->setUpdatedBy($currentUser);

        return array(
            'minimal not existing contact' => array(
                'sourceContact' => $minimalNotExistingContact,
                'expectedContact' => clone $minimalNotExistingContact
            ),
            'full existing contact' => array(
                'sourceContact' => $fullExistingContact,
                'expectedContact' => $expectedFullExistingContact
            ),
        );
    }
}
