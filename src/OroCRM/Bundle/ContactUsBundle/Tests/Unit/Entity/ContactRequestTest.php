<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Unit\Entity;


use OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest;

class ContactRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        new ContactRequest();
    }

    public function testSettersAndGetters()
    {
        /** @var Channel $channel */
        $channel = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel');
        $name = uniqid('name');
        $email = uniqid('@');
        $comment = uniqid('comment');
        $phone = uniqid('123123');
        $createdAt = new \DateTime();
        $updatedAt = new \DateTime();

        $request = new ContactRequest();
        $request->setChannel($channel);
        $request->setComment($comment);
        $request->setEmail($email);
        $request->setName($name);
        $request->setPhone($phone);
        $request->setCreatedAt($createdAt);
        $request->setUpdatedAt($updatedAt);

        $this->assertSame($channel, $request->getChannel());
        $this->assertEquals($comment, $request->getComment());
        $this->assertEquals($email, $request->getEmail());
        $this->assertEquals($name, $request->getName());
        $this->assertEquals($phone, $request->getPhone());
        $this->assertEquals($createdAt, $request->getCreatedAt());
        $this->assertEquals($updatedAt, $request->getUpdatedAt());
    }

    public function testBeforeSave()
    {
        $request = new ContactRequest();

        $this->assertNull($request->getCreatedAt());
        $this->assertNull($request->getUpdatedAt());

        $request->beforeSave();
        $this->assertNotNull($request->getCreatedAt());
        $this->assertInstanceOf('DateTime', $request->getCreatedAt());
        $this->assertNotNull($request->getUpdatedAt());
        $this->assertInstanceOf('DateTime', $request->getUpdatedAt());
        $this->assertSame($request->getCreatedAt(), $request->getUpdatedAt());
    }

    public function testDoPreUpdate()
    {
        $request = new ContactRequest();
        $updatedAt = new \DateTime();
        $request->setUpdatedAt($updatedAt);

        $request->doPreUpdate();
        $this->assertNotNull($request->getUpdatedAt());
        $this->assertInstanceOf('DateTime', $request->getUpdatedAt());
        $this->assertNotSame($updatedAt, $request->getUpdatedAt());
    }
}
