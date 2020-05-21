<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractEntityTestCase extends \PHPUnit\Framework\TestCase
{
    const TEST_ID = 123;

    protected $entity;

    protected function setUp(): void
    {
        $name         = $this->getEntityFQCN();
        $this->entity = new $name();
    }

    protected function tearDown(): void
    {
        unset($this->entity);
    }

    /**
     * @dataProvider  getSetDataProvider
     *
     * @param string $property
     * @param mixed  $value
     * @param mixed  $expected
     */
    public function testSetGet($property, $value = null, $expected = null)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if ($value !== null) {
            $propertyAccessor->setValue($this->entity, $property, $value);
        }
        $this->assertEquals($expected, $propertyAccessor->getValue($this->entity, $property));
    }

    /**
     * @return array
     */
    abstract public function getSetDataProvider();

    /**
     * @return string
     */
    abstract public function getEntityFQCN();
}
