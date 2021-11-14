<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new Opportunity();

        call_user_func([$obj, 'set' . ucfirst($property)], $value);
        $this->assertEquals($expected, call_user_func_array([$obj, 'get' . ucfirst($property)], []));
    }

    public function getSetDataProvider(): array
    {
        $organization = $this->createMock(Organization::class);

        return [
            'organization' => ['organization', $organization, $organization]
        ];
    }

    public function testGetEmail()
    {
        $opportunity = new Opportunity();
        $contact = $this->createMock(Contact::class);

        $this->assertNull($opportunity->getEmail());

        $opportunity->setContact($contact);
        $contact->expects($this->once())
            ->method('getEmail')
            ->willReturn('email@example.com');
        $this->assertEquals('email@example.com', $opportunity->getEmail());
    }
}
