<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Entity;

use Oro\Bundle\CaseBundle\Entity\CasePriority;
use PHPUnit\Framework\TestCase;

class CasePriorityTest extends TestCase
{
    private CasePriority $casePriority;

    #[\Override]
    protected function setUp(): void
    {
        $this->casePriority = new CasePriority('test');
    }

    public function testGetName(): void
    {
        $this->assertEquals('test', $this->casePriority->getName());
    }

    public function testLabel(): void
    {
        $this->assertNull($this->casePriority->getLabel());

        $label = 'email';

        $this->assertEquals($this->casePriority, $this->casePriority->setLabel($label));
        $this->assertEquals($label, $this->casePriority->getLabel());
    }

    public function testOrder(): void
    {
        $this->assertNull($this->casePriority->getOrder());

        $order = 100;

        $this->assertEquals($this->casePriority, $this->casePriority->setOrder($order));
        $this->assertEquals($order, $this->casePriority->getOrder());
    }

    public function testLocale(): void
    {
        $this->assertNull($this->casePriority->getLocale());

        $locale = 'en';

        $this->assertEquals($this->casePriority, $this->casePriority->setLocale($locale));
        $this->assertEquals($locale, $this->casePriority->getLocale());
    }
}
