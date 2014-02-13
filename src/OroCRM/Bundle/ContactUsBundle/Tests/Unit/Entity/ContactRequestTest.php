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
        $name      = uniqid('name');
        $email     = uniqid('@');
        $phone     = uniqid('123123');
        $comment   = uniqid('comment');
        $createdAt = new \DateTime();
        $updatedAt = new \DateTime();
        /** @var Channel $channel */
        $channel = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel');

        $request = new ContactRequest();
        $request->setName($name);
        $request->setEmailAddress($email);
        $request->setPhone($phone);
        $request->setComment($comment);
        $request->setCreatedAt($createdAt);
        $request->setUpdatedAt($updatedAt);

        $this->assertNull($request->getChannel());
        $request->setChannel($channel);

        $this->assertNull($request->getId());
        $this->assertSame($channel, $request->getChannel());
        $this->assertEquals($email, $request->getEmailAddress());
        $this->assertEquals($name, $request->getName());
        $this->assertEquals($phone, $request->getPhone());
        $this->assertEquals($comment, $request->getComment());
        $this->assertEquals($createdAt, $request->getCreatedAt());
        $this->assertEquals($updatedAt, $request->getUpdatedAt());
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
