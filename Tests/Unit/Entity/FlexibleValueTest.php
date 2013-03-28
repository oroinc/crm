<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Media;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

use Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\FlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Tests\Unit\Entity\Demo\Flexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Doctrine\Common\Collections\ArrayCollection;

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
     *
     * @param string $backendType the attribute backend type
     * @param mixed  $data        the value data
     *
     * @dataProvider valueProvider
     */
    public function testGetData($backendType, $data)
    {
        $this->value->getAttribute()->setBackendType($backendType);

        if ($backendType === AbstractAttributeType::BACKEND_TYPE_OPTIONS) {
            $this->assertTrue($this->value->getData() instanceof ArrayCollection);
            $this->assertEquals($this->value->getData()->count(), 0);
        } else {
            $this->assertNull($this->value->getData());
        }

        $this->value->setData($data);
        if ($backendType === AbstractAttributeType::BACKEND_TYPE_OPTIONS) {
            $this->assertTrue($this->value->getData() instanceof ArrayCollection);
            $this->assertEquals($this->value->getData()->count(), 1);
        } else {
            $this->assertEquals($this->value->getData(), $data);
        }
    }

    /**
     * Data provider
     *
     * @return multitype:multitype:number string
     *
     * @static
     */
    public static function valueProvider()
    {
        $options = new ArrayCollection();
        $option  = new AttributeOption();
        $options->add($option);

        return array(
            array(AbstractAttributeType::BACKEND_TYPE_TEXT, 'my really loooonnnng text'),
            array(AbstractAttributeType::BACKEND_TYPE_VARCHAR, 'my value'),
            array(AbstractAttributeType::BACKEND_TYPE_INTEGER, 12),
            array(AbstractAttributeType::BACKEND_TYPE_DECIMAL, 123.45),
            array(AbstractAttributeType::BACKEND_TYPE_DATE, '2013-03-28'),
            array(AbstractAttributeType::BACKEND_TYPE_DATETIME, '2013-03-28 06:01:01'),
            array(AbstractAttributeType::BACKEND_TYPE_OPTION, $option),
            array(AbstractAttributeType::BACKEND_TYPE_OPTIONS, $options),
            array(AbstractAttributeType::BACKEND_TYPE_MEDIA, new Media())
        );
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
