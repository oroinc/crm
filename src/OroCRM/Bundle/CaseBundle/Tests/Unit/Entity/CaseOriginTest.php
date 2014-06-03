<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Unit\Entity;

use OroCRM\Bundle\CaseBundle\Entity\CaseOrigin;

class CaseOriginTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        new CaseOrigin();
    }
    /**
     * @dataProvider settersAndGettersDataProvider
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new CaseOrigin();

        $result = call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertInstanceOf(get_class($obj), $result);
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function settersAndGettersDataProvider()
    {
        return array(
            array('label', 'email'),
            array('name', CaseOrigin::ORIGIN_EMAIL)
        );
    }
}
