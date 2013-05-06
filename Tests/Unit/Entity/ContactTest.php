<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;


use Oro\Bundle\ContactBundle\Entity\Contact;

class ContactTest extends \PHPUnit_Framework_TestCase
{
    public function testBeforeSave()
    {
        $entity = new Contact();
        $entity->beforeSave();
        $this->assertInstanceOf('\DateTime', $entity->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $entity->getUpdatedAt());
    }
}
