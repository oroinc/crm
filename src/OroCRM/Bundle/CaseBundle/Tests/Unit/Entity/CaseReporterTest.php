<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Unit\Entity;

use OroCRM\Bundle\CaseBundle\Entity\CaseReporter;

class CaseReporterTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        new CaseReporter();
    }
    /**
     * @dataProvider settersAndGettersDataProvider
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new CaseReporter();

        $result = call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertInstanceOf(get_class($obj), $result);
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function settersAndGettersDataProvider()
    {
        return array(
            array('id', 42),
            array('user', $this->getMock('Oro\Bundle\UserBundle\Entity\User')),
            array('contact', $this->getMock('OroCRM\Bundle\ContactBundle\Entity\Contact')),
            array('customer', $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Customer'))
        );
    }
}
