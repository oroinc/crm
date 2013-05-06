<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Entity;


use Oro\Bundle\AccountBundle\Entity\Account;

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
    }

    public function testDoPreUpdate()
    {
        $entity = new Account();
        $entity->doPreUpdate();
        $this->assertInstanceOf('\DateTime', $entity->getUpdatedAt());
    }
}
