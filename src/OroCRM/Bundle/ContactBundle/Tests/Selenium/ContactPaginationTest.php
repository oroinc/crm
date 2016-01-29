<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use Oro\Bundle\UserBundle\Tests\Selenium\Pages\Login;
use OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages\Contacts;

class ContactPaginationTest extends Selenium2TestCase
{
    /**
     * Test implement mass delete action if grid of Contacts will not be empty
     */
    public function testContactsMassDelete()
    {
        $login = $this->login();
        /** Precondition - mass delete all existing contact */
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->massDelete()
            ->assertNoDataMessageAndDisplayed('No records found');
    }

    /**
     * Test creates three contacts and test pagination functionality between them
     * @depends testContactsMassDelete
     * @return string
     */
    public function testContactsPaginationSwitching()
    {
        $firstContact = array(
            'firstName' => 'Aaron',
            'secondName' => 'Booker',
            'email' => 'aaron@mail.com'
        );

        $secondContact = array(
            'firstName' => 'Brendon',
            'secondName' => 'Payne',
            'email' => 'brandon@mail.com'
        );

        $thirdContact = array(
            'firstName' => 'Vincent',
            'secondName' => 'Brown',
            'email' => 'vincent@mail.com'
        );

        $firstContactTitle = $firstContact['firstName'].' '.$firstContact['secondName'].' - Contacts - Customers';
        $secondContactTitle = $secondContact['firstName'].' '.$secondContact['secondName'].' - Contacts - Customers';
        $thirdContactTitle = $thirdContact['firstName'].' '.$thirdContact['secondName'].' - Contacts - Customers';

        $login = $this->login();
        $this->createContact($login, $firstContact);
        $this->createContact($login, $secondContact);
        $this->createContact($login, $thirdContact);

        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            /** Sorting by email in ascending order*/
            ->sortBy('Email', 'asc')
            /** Open first contact and check if it correct */
            ->open([$firstContact['email']])
            ->assertTitle($firstContactTitle)
            ->checkEntityFieldData('Emails', $firstContact['email'])
            /** Click NEXT it should be second contact */
            ->switchEntityPagination('Next')
            ->assertTitle($secondContactTitle)
            ->checkEntityFieldData('Emails', $secondContact['email'])
            /** Click NEXT it should be third contact */
            ->switchEntityPagination('Next')
            ->assertTitle($thirdContactTitle)
            ->checkEntityFieldData('Emails', $thirdContact['email'])
            /** Click FIRST it should be first contact */
            ->switchEntityPagination('First')
            ->assertTitle($firstContactTitle)
            ->checkEntityFieldData('Emails', $firstContact['email'])
            /** Click LAST it should be third contact */
            ->switchEntityPagination('Last')
            ->assertTitle($thirdContactTitle)
            ->checkEntityFieldData('Emails', $thirdContact['email'])
            /** Click PREVIOUS it should be second contact */
            ->switchEntityPagination('Previous')
            ->assertTitle($secondContactTitle)
            ->checkEntityFieldData('Emails', $secondContact['email']);
            /** Mass delete all created contacts */
            /** @var Contacts $login */
            $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->massDelete();
    }

    /**
     * @param Login $login
     * @param $contactData
     */
    public function createContact(Login $login, $contactData)
    {
        /** @var Contacts $login */
        $login->openContacts('OroCRM\Bundle\ContactBundle')
            ->add()
            ->setFirstName($contactData['firstName'])
            ->setLastName($contactData['secondName'])
            ->setOwner('admin')
            ->setEmail($contactData['email'])
            ->save()
            ->assertMessage('Contact saved');
    }
}
