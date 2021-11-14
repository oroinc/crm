<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Entity;

use Oro\Bundle\CaseBundle\Entity\CaseStatus;

class CaseStatusTest extends \PHPUnit\Framework\TestCase
{
    /** @var CaseStatus */
    private $caseStatus;

    protected function setUp(): void
    {
        $this->caseStatus = new CaseStatus('test');
    }

    public function testGetName()
    {
        $this->assertEquals('test', $this->caseStatus->getName());
    }

    public function testLabel()
    {
        $this->assertNull($this->caseStatus->getLabel());

        $label = 'email';

        $this->assertEquals($this->caseStatus, $this->caseStatus->setLabel($label));
        $this->assertEquals($label, $this->caseStatus->getLabel());
    }

    public function testOrder()
    {
        $this->assertNull($this->caseStatus->getOrder());

        $order = 100;

        $this->assertEquals($this->caseStatus, $this->caseStatus->setOrder($order));
        $this->assertEquals($order, $this->caseStatus->getOrder());
    }

    public function testLocale()
    {
        $this->assertNull($this->caseStatus->getLocale());

        $locale = 'en';

        $this->assertEquals($this->caseStatus, $this->caseStatus->setLocale($locale));
        $this->assertEquals($locale, $this->caseStatus->getLocale());
    }
}
