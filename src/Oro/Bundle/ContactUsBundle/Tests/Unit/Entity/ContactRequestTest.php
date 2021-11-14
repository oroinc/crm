<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class ContactRequestTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        new ContactRequest();
    }

    public function testSettersAndGetters()
    {
        $firstName = uniqid('firstName');
        $lastName = uniqid('lastName');
        $fullName = sprintf('%s %s', $firstName, $lastName);
        $email = uniqid('@');
        $comment = uniqid('comment');
        $organizationName = uniqid('organizationName');
        $preferredContactMethod = uniqid('preferredContactMethod');
        $feedback = uniqid('feedback');
        $phone = uniqid('123123');
        $createdAt = new \DateTime();
        $updatedAt = new \DateTime();
        $lead = $this->createMock(Lead::class);
        $opportunity = $this->createMock(Opportunity::class);
        $contactReason = $this->createMock(ContactReason::class);

        $request = new ContactRequest();
        $request->setComment($comment);
        $request->setFeedback($feedback);
        $request->setEmailAddress($email);
        $request->setFirstName($firstName);
        $request->setLastName($lastName);
        $request->setPhone($phone);
        $request->setOrganizationName($organizationName);
        $request->setPreferredContactMethod($preferredContactMethod);

        $request->setCreatedAt($createdAt);
        $request->setUpdatedAt($updatedAt);

        $this->assertNull($request->getContactReason());
        $this->assertNull($request->getLead());
        $this->assertNull($request->getOpportunity());

        $request->setLead($lead);
        $request->setOpportunity($opportunity);
        $request->setContactReason($contactReason);

        $this->assertNull($request->getId());
        $this->assertSame($contactReason, $request->getContactReason());
        $this->assertEquals($comment, $request->getComment());
        $this->assertEquals($feedback, $request->getFeedback());
        $this->assertEquals($organizationName, $request->getOrganizationName());
        $this->assertEquals($email, $request->getEmailAddress());
        $this->assertEquals($firstName, $request->getFirstName());
        $this->assertEquals($lastName, $request->getLastName());
        $this->assertEquals($fullName, $request->getFullName());
        $this->assertEquals($fullName, (string)$request);
        $this->assertEquals($phone, $request->getPhone());
        $this->assertEquals($preferredContactMethod, $request->getPreferredContactMethod());
        $this->assertEquals($createdAt, $request->getCreatedAt());
        $this->assertEquals($updatedAt, $request->getUpdatedAt());
        $this->assertSame($lead, $request->getLead());
        $this->assertSame($opportunity, $request->getOpportunity());

        // should not provoke fatal error, because it's not mandatory field
        $request->setContactReason(null);
    }

    public function testBeforeSave()
    {
        $request = new ContactRequest();

        $this->assertNull($request->getCreatedAt());
        $this->assertNull($request->getUpdatedAt());

        $request->prePersist();
        $this->assertNotNull($request->getCreatedAt());
        $this->assertInstanceOf('DateTime', $request->getCreatedAt());
        $this->assertNotNull($request->getUpdatedAt());
        $this->assertInstanceOf('DateTime', $request->getUpdatedAt());
        $this->assertEquals($request->getCreatedAt(), $request->getUpdatedAt());
        $this->assertNotSame($request->getCreatedAt(), $request->getUpdatedAt());
    }

    public function testDoPreUpdate()
    {
        $request   = new ContactRequest();
        $updatedAt = new \DateTime();
        $request->setUpdatedAt($updatedAt);

        $request->preUpdate();
        $this->assertNotNull($request->getUpdatedAt());
        $this->assertInstanceOf('DateTime', $request->getUpdatedAt());
        $this->assertNotSame($updatedAt, $request->getUpdatedAt());
    }

    public function testGetEmail()
    {
        $request = new ContactRequest();

        $this->assertNull($request->getEmail());

        $request->setEmailAddress('email@example.com');
        $this->assertEquals('email@example.com', $request->getEmail());
    }
}
