<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityTest extends \PHPUnit\Framework\TestCase
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
        $organization = $this->createMock('Oro\Bundle\OrganizationBundle\Entity\Organization');
        return array(
            'organization' => array('organization', $organization, $organization)
        );
    }

    public function testGetEmail()
    {
        $opportunity = new Opportunity();
        $contact = $this->getMockBuilder('Oro\Bundle\ContactBundle\Entity\Contact')
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
