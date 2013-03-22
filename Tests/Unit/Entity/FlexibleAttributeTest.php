<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity;

use Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\FlexibleAttribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue;

/**
 * Test related demo class, aims to cover abstract one
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleAttributeTest extends \PHPUnit_Framework_TestCase
{

    protected $attribute;

    protected $flexibleAttribute;

    /**
     * Set up unit test
     */
    public function setUp()
    {
        // create attribute
        $this->attribute = new Attribute();
        $this->attribute->setCode('mycode');
        //  create flexible attribute
        $this->flexibleAttribute = new FlexibleAttribute();
        $this->flexibleAttribute->setAttribute($this->attribute);
    }

    /**
     * Test related method
     */
    public function testGetId()
    {
        $myid = 123;
        $this->flexibleAttribute->setId(123);
        $this->assertEquals($this->flexibleAttribute->getId(), 123);
    }

    /**
     * Test related method
     */
    public function testGetName()
    {
        $myname = 'my name';
        $this->flexibleAttribute->setName($myname);
        $this->assertEquals($this->flexibleAttribute->getName(), $myname);
    }

    /**
     * Test related method
     */
    public function testGetDescription()
    {
        $mydesc = 'my description';
        $this->flexibleAttribute->setDescription($mydesc);
        $this->assertEquals($this->flexibleAttribute->getDescription(), $mydesc);
    }

    /**
     * Test related method
     */
    public function testGetAttribute()
    {
        $this->assertEquals($this->flexibleAttribute->getAttribute(), $this->attribute);
    }

    /**
     * Test related method
     */
    public function testGetCode()
    {
        $code = 'myattcode';
        $this->flexibleAttribute->setCode($code);
        $this->assertEquals($this->flexibleAttribute->getCode(), $code);
    }

    /**
     * Test related method
     */
    public function testGetBackendType()
    {
        $type = 'varchar';
        $this->flexibleAttribute->setBackendType($type);
        $this->assertEquals($this->flexibleAttribute->getBackendType(), $type);
    }

    /**
     * Test related method
     */
    public function testGetRequired()
    {
        // false by default
        $this->assertFalse($this->flexibleAttribute->getRequired());
        $this->flexibleAttribute->setRequired(true);
        $this->assertTrue($this->flexibleAttribute->getRequired());
    }

    /**
     * Test related method
     */
    public function testGetUnique()
    {
        // false by default
        $this->assertFalse($this->flexibleAttribute->getUnique());
        $this->flexibleAttribute->setUnique(true);
        $this->assertTrue($this->flexibleAttribute->getUnique());
    }

    /**
     * Test related method
     */
    public function testTranslatable()
    {
        // false by default
        $this->assertFalse($this->flexibleAttribute->getTranslatable());
        $this->flexibleAttribute->setTranslatable(true);
        $this->assertTrue($this->flexibleAttribute->getTranslatable());
    }

    /**
     * Test related method
     */
    public function testSearchable()
    {
        // false by default
        $this->assertFalse($this->flexibleAttribute->getSearchable());
        $this->flexibleAttribute->setSearchable(true);
        $this->assertTrue($this->flexibleAttribute->getSearchable());
    }

    /**
     * Test related method
     */
    public function testScopable()
    {
        // false by default
        $this->assertFalse($this->flexibleAttribute->getScopable());
        $this->flexibleAttribute->setScopable(true);
        $this->assertTrue($this->flexibleAttribute->getScopable());
    }

    /**
     * Test related method
     */
    public function testDefaultValue()
    {
        // null by default
        $this->assertNull($this->flexibleAttribute->getDefaultValue());
        $myvalue = 'my default value';
        $this->flexibleAttribute->setDefaultValue($myvalue);
        $this->assertEquals($this->flexibleAttribute->getDefaultValue(), $myvalue);
    }

    /**
     * Test related method
     */
    public function testGetOptions()
    {
        // option
        $option = new AttributeOption();
        // option value
        $optionValue = new AttributeOptionValue();
        $option->addOptionValue($optionValue);
        $this->flexibleAttribute->addOption($option);
        $this->assertEquals($this->flexibleAttribute->getOptions()->count(), 1);
        $this->flexibleAttribute->removeOption($option);
        $this->assertEquals($this->flexibleAttribute->getOptions()->count(), 0);
    }
}
