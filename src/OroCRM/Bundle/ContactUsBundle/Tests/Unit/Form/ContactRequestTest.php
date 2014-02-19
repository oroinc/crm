<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Unit\Entity;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest;

class ContactRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        new ContactRequest();
    }

    public function testSettersAndGetters()
    {
        $firstName              = uniqid('firstName');
        $lastName               = uniqid('lastName');
        $email                  = uniqid('@');
        $comment                = uniqid('comment');
        $organizationName       = uniqid('organizationName');
        $preferredContactMethod = uniqid('preferredContactMethod');
        $feedback               = uniqid('feedback');
        $phone                  = uniqid('123123');
        $createdAt              = new \DateTime();
        $updatedAt              = new \DateTime();
        $lead                   = $this->getMock('OroCRM\Bundle\SalesBundle\Entity\Lead');
        $opportunity            = $this->getMock('OroCRM\Bundle\SalesBundle\Entity\Opportunity');
        $call                   = $this->getMock('OroCRM\Bundle\CallBundle\Entity\Call');
        $emailEntity            = $this->getMock('Oro\Bundle\EmailBundle\Entity\Email');
        $workflowStep           = $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowStep');
        $workflowItem           = $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem');
        /** @var Channel $channel */
        $channel                = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel');
        $contactReason          = $this->getMock(
            'OroCRM\Bundle\ContactUsBundle\Entity\ContactReason',
            [],
            [uniqid('label')]
        );

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


        $this->assertNull($request->getWorkflowStep());
        $this->assertNull($request->getWorkflowStep());
        $this->assertNull($request->getChannel());
        $this->assertNull($request->getContactReason());
        $this->assertNull($request->getLead());
        $this->assertNull($request->getOpportunity());
        $this->assertFalse($request->hasCall($call));
        $this->assertFalse($request->hasEmail($emailEntity));

        $request->setLead($lead);
        $request->setOpportunity($opportunity);
        $request->addCall($call);
        $request->addEmail($emailEntity);
        $request->setChannel($channel);
        $request->setContactReason($contactReason);
        $request->setWorkflowItem($workflowItem);
        $request->setWorkflowStep($workflowStep);

        $this->assertNull($request->getId());
        $this->assertSame($channel, $request->getChannel());
        $this->assertSame($contactReason, $request->getContactReason());
        $this->assertEquals($comment, $request->getComment());
        $this->assertEquals($feedback, $request->getFeedback());
        $this->assertEquals($organizationName, $request->getOrganizationName());
        $this->assertEquals($email, $request->getEmailAddress());
        $this->assertEquals($firstName, $request->getFirstName());
        $this->assertEquals($lastName, $request->getLastName());
        $this->assertEquals($phone, $request->getPhone());
        $this->assertEquals($preferredContactMethod, $request->getPreferredContactMethod());
        $this->assertEquals($createdAt, $request->getCreatedAt());
        $this->assertEquals($updatedAt, $request->getUpdatedAt());
        $this->assertSame($lead, $request->getLead());
        $this->assertSame($opportunity, $request->getOpportunity());
        $this->assertSame($workflowStep, $request->getWorkflowStep());
        $this->assertSame($workflowItem, $request->getWorkflowItem());

        // should not provoke fatal error, because it's not mandatory field
        $request->setContactReason(null);

        $request->removeCall($call);
        $this->assertCount(0, $request->getCalls());
        $request->removeEmail($emailEntity);
        $this->assertCount(0, $request->getEmails());
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
        $this->assertSame($request->getCreatedAt(), $request->getUpdatedAt());
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
}
