<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\CampaignBundle\Form\Type\CodeType;

class CodeTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetParent()
    {
        $codeType = new CodeType();
        $this->assertEquals($codeType->getParent(), 'text');
        $this->assertEquals($codeType->getName(), 'orocrm_campaign_code_type');
    }
}
