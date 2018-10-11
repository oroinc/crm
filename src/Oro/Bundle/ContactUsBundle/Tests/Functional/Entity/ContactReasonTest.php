<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Functional\Entity;

use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ContactReasonTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();
    }

    public function testSettersAndGetters()
    {
        $label = uniqid('label');
        $entity = new ContactReason($label);

        $this->assertNull($entity->getId());
        $this->assertSame($label, $entity->getDefaultTitle()->getString());
        $this->assertSame($label, (string)$entity);
    }
}