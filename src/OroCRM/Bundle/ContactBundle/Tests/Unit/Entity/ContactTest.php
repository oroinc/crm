<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Entity;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;

class ContactTest extends \PHPUnit_Framework_TestCase
{
    public function testBeforeSave()
    {
        $entity = new Contact();
        $entity->beforeSave();
        $this->assertInstanceOf('\DateTime', $entity->getCreatedAt());
    }

    public function testDoPreUpdate()
    {
        $entity = new Contact();
        $entity->doPreUpdate();
        $this->assertInstanceOf('\DateTime', $entity->getUpdatedAt());
    }

    public function testAddAccount()
    {
        $account = new Account();
        $account->setId(1);

        $contact = new Contact();
        $contact->setId(2);

        $this->assertEmpty($contact->getAccounts()->toArray());

        $contact->addAccount($account);
        $actualAccounts = $contact->getAccounts()->toArray();
        $this->assertCount(1, $actualAccounts);
        $this->assertEquals($account, current($actualAccounts));
    }

    public function testRemoveAccount()
    {
        $account = new Account();
        $account->setId(1);

        $contact = new Contact();
        $contact->setId(2);

        $contact->addAccount($account);
        $this->assertCount(1, $contact->getAccounts()->toArray());

        $contact->removeAccount($account);
        $this->assertEmpty($contact->getAccounts()->toArray());
    }

    public function testAddresses()
    {
        $addressOne = new ContactAddress();
        $addressOne->setCountry('US');
        $addressTwo = new ContactAddress();
        $addressTwo->setCountry('UK');
        $addressThree = new ContactAddress();
        $addressThree->setCountry('RU');
        $addresses = array($addressOne, $addressTwo);

        $contact = new Contact();
        $this->assertSame($contact, $contact->setAddresses($addresses));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($addresses, $actual->toArray());

        $this->assertSame($contact, $contact->addAddress($addressTwo));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals($addresses, $actual->toArray());

        $this->assertSame($contact, $contact->addAddress($addressThree));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array($addressOne, $addressTwo, $addressThree), $actual->toArray());

        $this->assertSame($contact, $contact->removeAddress($addressOne));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $addressTwo, 2 => $addressThree), $actual->toArray());

        $this->assertSame($contact, $contact->removeAddress($addressOne));
        $actual = $contact->getAddresses();
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $actual);
        $this->assertEquals(array(1 => $addressTwo, 2 => $addressThree), $actual->toArray());
    }

    public function testGetAttributeDataException()
    {
        $contact = new Contact();
        $attribute = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute')
            ->setMethods(array('getCode'))
            ->getMockForAbstractClass();
        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('first_name'));

        $firstNameVal = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface')
            ->setMethods(array('setEntity', 'getData', 'getAttribute'))
            ->getMock();
        $firstNameVal->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));
        $firstNameVal->expects($this->once())
            ->method('getData')
            ->will(
                $this->returnCallback(
                    function () {
                        throw new \Exception('TEST');
                    }
                )
            );
        $contact->addValue($firstNameVal);
        $this->assertEquals('', $contact->getAttributeData('first_name'));
    }

    public function testToStringNoAttributes()
    {
        $contact = new Contact();
        $this->assertEquals('', $contact->__toString());
    }

    public function testNames()
    {
        $contact = new Contact();
        $attributeFN = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute')
            ->setMethods(array('getCode'))
            ->getMockForAbstractClass();
        $attributeFN->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('first_name'));

        $firstNameVal = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface')
            ->setMethods(array('setEntity', 'getData', 'getAttribute'))
            ->getMock();
        $firstNameVal->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attributeFN));
        $firstNameVal->expects($this->any())
            ->method('getData')
            ->will($this->returnValue('First'));
        $contact->addValue($firstNameVal);

        $attributeLN = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute')
            ->setMethods(array('getCode'))
            ->getMockForAbstractClass();
        $attributeLN->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue('last_name'));

        $lastNameVal = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface')
            ->setMethods(array('setEntity', 'getData', 'getAttribute'))
            ->getMock();
        $lastNameVal->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attributeLN));
        $lastNameVal->expects($this->any())
            ->method('getData')
            ->will($this->returnValue('Last'));
        $contact->addValue($lastNameVal);
        $this->getAttributeDataTest($contact);
        $this->toStringTest($contact);
        $this->getFullNameTest($contact);
    }

    /**
     * @param \OroCRM\Bundle\ContactBundle\Entity\Contact $contact
     */
    protected function getAttributeDataTest($contact)
    {
        $this->assertEquals('First', $contact->getAttributeData('first_name'));
    }

    /**
     * @param \OroCRM\Bundle\ContactBundle\Entity\Contact $contact
     */
    protected function toStringTest($contact)
    {
        $this->assertEquals('First Last', $contact->__toString());
    }

    /**
     * @param \OroCRM\Bundle\ContactBundle\Entity\Contact $contact
     */
    protected function getFullNameTest($contact)
    {
        $this->assertEquals($contact->getFullname(), sprintf('%s %s', 'First', 'Last'));

        $contact->setNameFormat('%last%, %first%');

        $this->assertEquals($contact->getFullname(), sprintf('%s, %s', 'Last', 'First'));
    }
}
