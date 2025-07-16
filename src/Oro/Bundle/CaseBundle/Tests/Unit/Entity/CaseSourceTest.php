<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Entity;

use Oro\Bundle\CaseBundle\Entity\CaseSource;
use PHPUnit\Framework\TestCase;

class CaseSourceTest extends TestCase
{
    private CaseSource $caseSource;

    #[\Override]
    protected function setUp(): void
    {
        $this->caseSource = new CaseSource('test');
    }

    public function testGetName(): void
    {
        $this->assertEquals('test', $this->caseSource->getName());
    }

    public function testLabel(): void
    {
        $this->assertNull($this->caseSource->getLabel());

        $label = 'email';

        $this->assertEquals($this->caseSource, $this->caseSource->setLabel($label));
        $this->assertEquals($label, $this->caseSource->getLabel());
    }

    public function testLocale(): void
    {
        $this->assertNull($this->caseSource->getLocale());

        $locale = 'en';

        $this->assertEquals($this->caseSource, $this->caseSource->setLocale($locale));
        $this->assertEquals($locale, $this->caseSource->getLocale());
    }
}
