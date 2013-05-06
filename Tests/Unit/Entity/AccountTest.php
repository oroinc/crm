<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;


use Oro\Bundle\ContactBundle\Entity\Account;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    public function testGettersSetters()
    {
        $entity = new Account();
        $entity->setName('Test');
        $this->assertEquals('Test', $entity->getName());
        $this->assertEquals('Test', (string)$entity);
    }

    public function testBeforeSave()
    {
        $entity = new Account();
        $entity->beforeSave();
        $this->assertInstanceOf('\DateTime', $entity->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $entity->getUpdatedAt());
    }
}
