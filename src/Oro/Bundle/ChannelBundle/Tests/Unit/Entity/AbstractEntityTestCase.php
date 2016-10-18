<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Entity;

abstract class AbstractEntityTestCase extends \PHPUnit_Framework_TestCase
{
    protected $entity;

    protected function setUp()
    {
        $name         = $this->getEntityFQCN();
        $this->entity = new $name();
    }

    public function tearDown()
    {
        unset($this->entity);
    }

    /**
     * @dataProvider  getDataProvider
     *
     * @param string $property
     * @param mixed  $value
     * @param mixed  $expected
     */
    public function testSetGet($property, $value = null, $expected = null)
    {
        if ($value !== null) {
            call_user_func([$this->entity, 'set' . ucfirst($property)], $value);
        }

        $this->assertEquals($expected, call_user_func([$this->entity, 'get' . ucfirst($property)]));
    }

    public function testEmptyIdConstruction()
    {
        $this->assertNull($this->entity->getId());
    }

    /**
     * @return array
     */
    abstract public function getDataProvider();

    /**
     * @return string
     */
    abstract public function getEntityFQCN();
}
