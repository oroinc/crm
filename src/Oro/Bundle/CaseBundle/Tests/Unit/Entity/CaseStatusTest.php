<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Entity;

use Oro\Bundle\CaseBundle\Entity\CaseStatus;
use PHPUnit\Framework\TestCase;

class CaseStatusTest extends TestCase
{
    private CaseStatus $caseStatus;

    #[\Override]
    protected function setUp(): void
    {
        $this->caseStatus = new CaseStatus('test');
    }

    public function testGetName(): void
    {
        $this->assertEquals('test', $this->caseStatus->getName());
    }

    public function testLabel(): void
    {
        $this->assertNull($this->caseStatus->getLabel());

        $label = 'email';

        $this->assertEquals($this->caseStatus, $this->caseStatus->setLabel($label));
        $this->assertEquals($label, $this->caseStatus->getLabel());
    }

    public function testOrder(): void
    {
        $this->assertNull($this->caseStatus->getOrder());

        $order = 100;

        $this->assertEquals($this->caseStatus, $this->caseStatus->setOrder($order));
        $this->assertEquals($order, $this->caseStatus->getOrder());
    }

    public function testLocale(): void
    {
        $this->assertNull($this->caseStatus->getLocale());

        $locale = 'en';

        $this->assertEquals($this->caseStatus, $this->caseStatus->setLocale($locale));
        $this->assertEquals($locale, $this->caseStatus->getLocale());
    }
}
