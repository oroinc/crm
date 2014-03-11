<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;

/**
 * Class CreateLeadTest
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Sales
 */
class CreateLeadTest extends Selenium2TestCase
{
    protected $address = array(
        'label' => 'Address Label',
        'street' => 'Address Street',
        'city' => 'Address City',
        'zipCode' => '10001',
        'country' => 'United States',
        'region' => 'New York'
    );

    /**
     * @return string
     */
    public function testCreateLead()
    {
        $name = 'Lead_'.mt_rand();

        $login = $this->login();
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->add()
            ->setName($name)
            ->setFirstName($name . '_first_name')
            ->setLastName($name . '_last_name')
            ->setJobTitle('Manager')
            ->setPhone('712-566-3002')
            ->setEmail($name . '@mail.com')
            ->setCompany('Some Company')
            ->setWebsite('http://www.orocrm.com')
            ->setEmployees('100')
            ->setOwner('admin')
            ->setAddress($this->address)
            ->save()
            ->assertMessage('Lead saved')
            ->toGrid()
            ->assertTitle('Leads - Sales');

        return $name;
    }

    /**
     * @depends testCreateLead
     * @param $name
     * @return string
     */
    public function testUpdateLead($name)
    {
        $newName = 'Update_' . $name;

        $login = $this->login();
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->filterBy('Lead name', $name)
            ->open(array($name))
            ->edit()
            ->assertTitle($name . ' - Edit - Leads - Sales')
            ->setName($newName)
            ->save()
            ->assertMessage('Lead saved')
            ->toGrid()
            ->assertTitle('Leads - Sales')
            ->close();

        return $newName;
    }

    /**
     * @depends testUpdateLead
     * @param $name
     */
    public function testDeleteLead($name)
    {
        $login = $this->login();
        $login->openLeads('OroCRM\Bundle\SalesBundle')
            ->filterBy('Lead name', $name)
            ->open(array($name))
            ->delete()
            ->assertTitle('Leads - Sales')
            ->assertMessage('Lead deleted')
            ->assertNoDataMessage('No records found');
    }
}
