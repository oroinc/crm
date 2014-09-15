<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Entity;

use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class AbstractEntityTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var Object */
    protected $entity;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $name         = $this->getEntityFQCN();
        $this->entity = new $name();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
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

        $this->assertEquals(
            $expected,
            $propertyAccessor->getValue($this->entity, $property)
        );
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
