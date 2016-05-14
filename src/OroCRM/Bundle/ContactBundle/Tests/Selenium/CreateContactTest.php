<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages\Contacts;

class CreateContactTest extends Selenium2TestCase
{
    protected $addressPrimary = array(
        'types' => array('billing'),
        'primary' => true,
        'firstName' => 'Address First Name',
        'lastName' => 'Address Last Name',
        'street' => 'Address Street',
        'city' => 'Address City',
        'postalCode' => '10001',
        'country' => 'United States',
        'region' => 'New York'
    );

    protected $addressSecondary = array(
        'types' => array('shipping'),
        'primary' => false,
        'firstName' => 'Address1 First Name',
        'lastName' => 'Address1 Last Name',
        'street' => 'Address1 Street',
        'city' => 'Address1 City',
        'postalCode' => '10001',
        'country' => 'United States',
        'region' => 'New York'
    );

    /**
     * @return string
     */
    public function testCreateContact()
    {
        $contactName = 'Contact_'.mt_rand();
        $addressPrimary = array();
        $addressSecondary = array();

        $login = $this->login();
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->assertTitle('All - Contacts - Customers')
            ->add()
            ->assertTitle('Create Contact - Contacts - Customers')
            ->setFirstName($contactName . '_first')
            ->setLastName($contactName . '_last')
            ->setOwner('admin')
            ->setEmail($contactName . '@mail.com')
            ->setAddress($this->addressPrimary)
            ->setAddress($this->addressSecondary, 2)
            ->save()
            ->assertMessage('Contact saved')
            ->toGrid()
            ->assertTitle('All - Contacts - Customers')
            ->close()
            ->filterBy('Email', $contactName . '@mail.com')
            ->open(array($contactName))
            ->assertTitle($contactName . '_first ' . $contactName . '_last' . ' - Contacts - Customers')
            ->edit()
            ->getAddress($addressPrimary)
            ->getAddress($addressSecondary, 1);

        static::assertEquals($this->addressPrimary, $addressPrimary);
        static::assertEquals($this->addressSecondary, $addressSecondary);

        return $contactName;
    }

    public function testAddAddress()
    {
        $contactName = 'Contact_' . mt_rand();
        $addressPrimary = array();

        $login = $this->login();
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->add()
            ->setFirstName($contactName . '_first')
            ->setLastName($contactName . '_last')
            ->setEmail($contactName . '@mail.com')
            ->setOwner('admin')
            ->save()
            ->assertMessage('Contact saved')
            ->toGrid()
            ->assertTitle('All - Contacts - Customers')
            ->close()
            ->filterBy('Email', $contactName . '@mail.com')
            ->open(array($contactName))
            ->setAddress($this->addressPrimary)
            ->toGrid()
            ->close()
            ->filterBy('Email', $contactName . '@mail.com')
            ->open(array($contactName))
            ->edit()
            ->getAddress($addressPrimary);

        static::assertEquals($this->addressPrimary, $addressPrimary);
    }

    /**
     * @depends testCreateContact
     * @param $contactName
     */
    public function testContactAutocomplete($contactName)
    {
        $login = $this->login();
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->add()
            ->setFirstName($contactName . '_first_autocomplete')
            ->setLastName($contactName . '_last_autocomplete')
            ->setOwner('admin')
            ->setAssignedTo('admin')
            ->setReportsTo($contactName)
            ->setAddressStreet('Street')
            ->setAddressCity('City')
            ->setAddressPostalCode('Zip Code 000')
            ->typeAddressCountry('Kazak')
            ->typeAddressRegion('Aqm')
            ->save()
            ->assertMessage('Contact saved')
            ->toGrid()
            ->assertTitle('All - Contacts - Customers')
            ->close();
    }

    /**
     * @depends testCreateContact
     * @param $contactName
     * @return string
     */
    public function testUpdateContact($contactName)
    {
        $newContactName = 'Update_' . $contactName;

        $login = $this->login();
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', $contactName . '@mail.com')
            ->open(array($contactName))
            ->assertTitle($contactName . '_first ' . $contactName . '_last' . ' - Contacts - Customers')
            ->edit()
            ->setFirstName($newContactName . '_first')
            ->setEmail($newContactName . '@mail.com')
            ->save()
            ->assertMessage('Contact saved')
            ->toGrid()
            ->assertTitle('All - Contacts - Customers')
            ->close();

        return $newContactName;
    }

    /**
     * @depends testUpdateContact
     * @param $contactName
     */
    public function testDeleteContact($contactName)
    {
        $login = $this->login();
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', $contactName . '@mail.com')
            ->open(array($contactName))
            ->delete()
            ->assertTitle('All - Contacts - Customers')
            ->assertMessage('Contact deleted');

        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->filterBy('Email', $contactName . '@mail.com')
            ->assertNoDataMessage('No contact was found to match your search');
    }

    public function testCloseWidgetWindow()
    {
        $login = $this->login();
        $login->closeWidgetWindow();
    }
}
