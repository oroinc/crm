<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Entity;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new Opportunity();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($expected, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function getSetDataProvider()
    {
        $organization = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');
        return array(
            'organization' => array('organization', $organization, $organization)
        );
    }

    public function testGetEmail()
    {
        $opportunity = new Opportunity();
        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertNull($opportunity->getEmail());

        $opportunity->setContact($contact);
        $contact->expects($this->once())
            ->method('getEmail')
            ->will($this->returnValue('email@example.com'));
        $this->assertEquals('email@example.com', $opportunity->getEmail());
    }
}
