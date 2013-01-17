<?php
namespace Oro\Bundle\FlexibleEntityBundle\Test\Entity;

use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class AttributeOptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @staticvar string
     */
    protected static $localeCode = 'en';

    /**
     * @staticvar string
     */
    protected static $localeCodeFr = 'fr';

    /**
     * @staticvar integer
     */
    protected static $id = 3;

    /**
     * @staticvar integer
     */
    protected static $sortOrder = 5;

    /**
     * @staticvar string
     */
    protected static $attCode = 'testAtt';

    /**
     * @staticvar string
     */
    protected static $attOptValueEn = 'testAttOptValueEn';

    /**
     * @staticvar string
     */
    protected static $attOptValueFr = 'testAttOptValueFr';

    /**
     * @staticvar string
     */
    protected static $attClass = 'Oro\Bundle\FlexibleEntityBundle\Entity\Attribute';

    /**
     * @staticvar string
     */
    protected static $attOptClass = 'Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption';

    /**
     * @staticvar string
     */
    protected static $attOptValueClass = 'Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue';

    /**
     * Test related getter/setter method
     */
    public function testId()
    {
        $attOpt = new AttributeOption();

        // assert default value is null
        $this->assertNull($attOpt->getId());

        // assert get/set
        $obj = $attOpt->setId(self::$id);
        $this->assertInstanceOf(self::$attOptClass, $obj);
        $this->assertEquals(self::$id, $attOpt->getId());
    }

    /**
     * Test related getter/setter method
     */
    public function testTranslatable()
    {
        $attOpt = new AttributeOption();

        // assert default value
        $this->assertFalse($attOpt->getTranslatable());

        // assert true value
        $obj = $attOpt->setTranslatable(true);
        $this->assertInstanceOf(self::$attOptClass, $obj);
        $this->assertTrue($attOpt->getTranslatable());

        // assert false value
        $obj = $attOpt->setTranslatable(false);
        $this->assertInstanceOf(self::$attOptClass, $obj);
        $this->assertFalse($attOpt->getTranslatable());
    }

    /**
     * Test related getter/setter method
     */
    public function testSortOrder()
    {
        $attOpt = new AttributeOption();

        // assert default value
        $this->assertEquals(1, $attOpt->getSortOrder());

        // assert get/set
        $obj = $attOpt->setSortOrder(self::$sortOrder);
        $this->assertInstanceOf(self::$attOptClass, $obj);
        $this->assertEquals(self::$sortOrder, $attOpt->getSortOrder());
    }

    /**
     * Test related getter/setter method
     */
    public function testLocaleCode()
    {
        $attOpt = new AttributeOption();

        // assert default value is null
        $this->assertNull($attOpt->getLocaleCode());

        // assert get/set
        $obj = $attOpt->setLocaleCode(self::$localeCode);
        $this->assertInstanceOf(self::$attOptClass, $obj);
        $this->assertEquals(self::$localeCode, $attOpt->getLocaleCode());
    }

    /**
     * Test related getter/setter method
     */
    public function testAttribute()
    {
        $attOpt = new AttributeOption();

        // assert default value
        $this->assertNull($attOpt->getAttribute());

        // assert get/set
        $att = new Attribute();
        $att->setCode(self::$attCode);
        $obj = $attOpt->setAttribute($att);

        $this->assertInstanceOf(self::$attOptClass, $obj);
        $this->assertInstanceOf(self::$attClass, $attOpt->getAttribute());
        $this->assertEquals($att, $attOpt->getAttribute());
        $this->assertEquals(self::$attCode, $attOpt->getAttribute()->getCode());
    }

    /**
     * Test related getter/setter method
     */
    public function testAttributeOptionValue()
    {
        $attOpt = new AttributeOption();

        // assert default values
        $this->assertCount(0, $attOpt->getOptionValues());

        // assert adding option
        $attOptValueEn = new AttributeOptionValue();
        $attOptValueEn->setValue(self::$attOptValueEn);
        $attOptValueEn->setLocaleCode(self::$localeCode);
        $attOpt->addOptionValue($attOptValueEn);

        // assert result
        $attOptValue = $attOpt->getOptionValue();
        $this->assertInstanceOf(self::$attOptValueClass, $attOptValue);
        $this->assertEquals(self::$localeCode, $attOptValue->getLocaleCode());
        $this->assertEquals(self::$attOptValueEn, $attOptValue->getValue());

        // add a second value and define option as translatable
        $attOpt->setTranslatable(true);
        $attOptValueFr = new AttributeOptionValue();
        $attOptValueFr->setValue(self::$attOptValueFr);
        $attOptValueFr->setLocaleCode(self::$localeCodeFr);
        $attOpt->setLocaleCode(self::$localeCodeFr);
        $obj = $attOpt->addOptionValue($attOptValueFr);

        // assertions getter
        $this->assertInstanceOf(self::$attOptClass, $obj);
        $this->assertCount(2, $attOpt->getOptionValues());

        // assert option value fr
        $attOptValue = $attOpt->getOptionValue();
        $this->assertInstanceOf(self::$attOptValueClass, $attOptValue);
        $this->assertEquals(self::$localeCodeFr, $attOptValue->getLocaleCode());
        $this->assertEquals(self::$attOptValueFr, $attOptValue->getValue());

        // assert option value en
        $attOpt->setLocaleCode(self::$localeCode);
        $attOptValue = $attOpt->getOptionValue();
        $this->assertInstanceOf(self::$attOptValueClass, $attOptValue);
        $this->assertEquals(self::$localeCode, $attOptValue->getLocaleCode());
        $this->assertEquals(self::$attOptValueEn, $attOptValue->getValue());
        $this->assertEquals(self::$attOptValueEn, $attOpt->__toString());

        // assert remove option value
        $obj = $attOpt->removeOptionValue($attOptValueEn);
        $this->assertInstanceOf(self::$attOptClass, $obj);
        $this->assertCount(1, $attOpt->getOptionValues());
        $this->assertFalse($attOpt->getOptionValue());

        $attOpt->setLocaleCode(self::$localeCodeFr);
        $attOptValue = $attOpt->getOptionValue();
        $this->assertInstanceOf(self::$attOptValueClass, $attOptValue);
        $this->assertEquals(self::$localeCodeFr, $attOptValue->getLocaleCode());
        $this->assertEquals(self::$attOptValueFr, $attOptValue->getValue());
        $this->assertEquals(self::$attOptValueFr, $attOpt->__toString());
    }

}
