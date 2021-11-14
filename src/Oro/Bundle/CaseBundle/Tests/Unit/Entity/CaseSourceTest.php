<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Entity;

use Oro\Bundle\CaseBundle\Entity\CaseSource;

class CaseSourceTest extends \PHPUnit\Framework\TestCase
{
    /** @var CaseSource */
    private $caseSource;

    protected function setUp(): void
    {
        $this->caseSource = new CaseSource('test');
    }

    public function testGetName()
    {
        $this->assertEquals('test', $this->caseSource->getName());
    }

    public function testLabel()
    {
        $this->assertNull($this->caseSource->getLabel());

        $label = 'email';

        $this->assertEquals($this->caseSource, $this->caseSource->setLabel($label));
        $this->assertEquals($label, $this->caseSource->getLabel());
    }

    public function testLocale()
    {
        $this->assertNull($this->caseSource->getLocale());

        $locale = 'en';

        $this->assertEquals($this->caseSource, $this->caseSource->setLocale($locale));
        $this->assertEquals($locale, $this->caseSource->getLocale());
    }
}
