<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactUsBundle\Entity\ContactReason;

class ContactReasonTest extends \PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $label = uniqid('label');
        $entity = new ContactReason($label);

        $this->assertNull($entity->getId());
        $this->assertSame($label, $entity->getLabel());

        $label2 = uniqid('label2');
        $entity->setLabel($label2);

        $this->assertSame($label2, $entity->getLabel());
        $this->assertSame($label2, (string)$entity);
    }
}
