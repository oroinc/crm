<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Entity;

use Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\FlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Flexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;

/**
 * Test related demo class, aims to cover abstract one
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleValueTest extends \PHPUnit_Framework_TestCase
{

    protected $flexible;

    protected $attribute;

    protected $value;

    /**
     * Set up unit test
     */
    public function setUp()
    {
        // create flexible
        $this->flexible = new Flexible();
        // create attribute
        $this->attribute = new Attribute();
        $this->attribute->setCode('mycode');
        $this->attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
        // create value
        $this->value = new FlexibleValue();
        $this->value->setAttribute($this->attribute);
        $this->value->setEntity($this->flexible);
    }

    /**
     * Test related method
     */
    public function testGetId()
    {
        $this->assertNull($this->value->getId());
    }

    /**
     * Test related method
     */
    public function testGetAttribute()
    {
        $this->assertEquals($this->value->getAttribute(), $this->attribute);
    }

    /**
     * Test related method
     */
    public function testGetLocale()
    {
        $code = 'fr_FR';
        $this->value->setLocale($code);
        $this->assertEquals($this->value->getLocale(), $code);
    }

    /**
     * Test related method
     */
    public function testGetScope()
    {
        $code = 'ecommerce';
        $this->value->setScope($code);
        $this->assertEquals($this->value->getScope(), $code);
    }

    /**
     * Test related method
     */
    public function testGetData()
    {
        $data = 'my value';
        $this->value->setData($data);
        $this->assertEquals($this->value->getData(), $data);
    }

    /**
     * Test related method
     */
    public function testGetUnit()
    {
        $unit = 'mm';
        $this->value->setUnit($unit);
        $this->assertEquals($this->value->getUnit(), $unit);
    }

    /**
     * Test related method
     */
    public function testGetCurrency()
    {
        $currency = 'USD';
        $this->value->setCurrency($currency);
        $this->assertEquals($this->value->getCurrency(), $currency);
    }

    /**
     * Test related method
     */
    public function testGetOption()
    {
        $option = new AttributeOption();
        $this->value->setOption($option);
        $this->assertEquals($this->value->getOption(), $option);
    }
}
