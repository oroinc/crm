<?php
namespace Oro\Bundle\FlexibleEntityBundle\Test\Entity;

use Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\FlexibleAttribute;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

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

}