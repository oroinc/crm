<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Annotation;

use Oro\Bundle\NavigationBundle\Annotation\TitleTemplate;

class TitleTemplateTest extends \PHPUnit_Framework_TestCase
{
    const TEST_VALUE = 'test annotation value';

    /**
     * @dataProvider provider
     *@param array $data
     */
    public function testAnnotation($data = array())
    {
        try {
            $annotation = new TitleTemplate($data);

            $this->assertEquals(self::TEST_VALUE, $annotation->getTitleTemplate());
        } catch (\Exception $e) {
            $this->assertInstanceOf('\RuntimeException', $e);
        }
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array(array('value' => self::TEST_VALUE)),
            array()
        );
    }
}
