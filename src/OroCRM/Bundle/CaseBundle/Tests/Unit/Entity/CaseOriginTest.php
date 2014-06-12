<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Unit\Entity;

use OroCRM\Bundle\CaseBundle\Entity\CaseOrigin;

class CaseOriginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CaseOrigin
     */
    protected $caseOrigin;

    protected function setUp()
    {
        $this->caseOrigin = new CaseOrigin('test');
    }

    public function testGetName()
    {
        $this->assertEquals('test', $this->caseOrigin->getName());
    }

    public function testLabel()
    {
        $this->assertNull($this->caseOrigin->getLabel());

        $label = 'email';

        $this->assertEquals($this->caseOrigin, $this->caseOrigin->setLabel($label));
        $this->assertEquals($label, $this->caseOrigin->getLabel());
    }

    public function testLocale()
    {
        $this->assertNull($this->caseOrigin->getLocale());

        $locale = 'en';

        $this->assertEquals($this->caseOrigin, $this->caseOrigin->setLocale($locale));
        $this->assertEquals($locale, $this->caseOrigin->getLocale());
    }
}
