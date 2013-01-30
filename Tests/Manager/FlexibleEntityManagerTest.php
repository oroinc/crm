<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Manager;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;

use Oro\Bundle\FlexibleEntityBundle\Tests\AbstractFlexibleEntityManagerTest;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleEntityManager;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class FlexibleEntityManagerTest extends AbstractFlexibleEntityManagerTest
{

    /**
     * Set up unit test
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * test related method
     */
    public function testConstructWithCustomEntityManager()
    {
        $myManager = new FlexibleEntityManager($this->container, $this->flexibleClassName, $this->entityManager);
        $this->assertNotNull($myManager->getStorageManager());
        $this->assertEquals($myManager->getStorageManager(), $this->entityManager);
    }

    /**
     * test related method
     */
    public function testGetStorageManager()
    {
        $this->assertNotNull($this->manager->getStorageManager());
        $this->assertTrue($this->manager->getStorageManager() instanceof EntityManager);
    }

    /**
     * test related method
     */
    public function testGetFlexibleConfig()
    {
        $this->assertNotNull($this->manager->getFlexibleConfig());
        $this->assertNotEmpty($this->manager->getFlexibleConfig());
        $this->assertEquals($this->manager->getFlexibleConfig(), $this->flexibleConfig['entities_config'][$this->flexibleClassName]);
    }

    /**
     * Test related method
     */
    public function testGetLocale()
    {
        // get default locale
        $this->assertEquals($this->manager->getLocale(), $this->defaultLocale);
        // forced locale
        $code = 'fr_FR';
        $this->manager->setLocale($code);
        $this->assertEquals($this->manager->getLocale(), $code);
    }

    /**
     * Test related method
     */
    public function testGetScope()
    {
        // get default scope
        $this->assertEquals($this->manager->getScope(), $this->defaultScope);
        // forced scope
        $code = 'ecommerce';
        $this->manager->setScope($code);
        $this->assertEquals($this->manager->getScope(), $code);
    }

    /**
     * Test related method
     */
    public function testGetAttributeName()
    {
        $this->assertEquals($this->manager->getAttributeName(), $this->attributeClassName);
    }

    /**
     * Test related method
     */
    public function testGetEntityAttributeName()
    {
        $this->assertEquals($this->manager->getEntityAttributeName(), $this->flexibleAttributeClassName);
    }

    /**
     * Test related method
     */
    public function testGetAttributeOptionName()
    {
        $this->assertEquals($this->manager->getAttributeOptionName(), $this->attributeOptionClassName);
    }

    /**
     * Test related method
     */
    public function testGetAttributeOptionValueName()
    {
        $this->assertEquals($this->manager->getAttributeOptionValueName(), $this->attributeOptionValueClassName);
    }

    /**
     * Test related method
     */
    public function testGetEntityValueName()
    {
        $this->assertEquals($this->manager->getEntityValueName(), $this->flexibleValueClassName);
    }

    /**
     * Test related method
     */
    public function testGetEntityRepository()
    {
        $this->assertTrue($this->manager->getEntityRepository() instanceof \Doctrine\ORM\EntityRepository);
    }

    /**
     * Test related method
     */
    public function testGetAttributeRepository()
    {
        $this->assertTrue($this->manager->getAttributeRepository() instanceof \Doctrine\ORM\EntityRepository);
    }

    /**
     * Test related method
     */
    public function testGetEntityAttributeRepository()
    {
        $this->assertTrue($this->manager->getEntityAttributeRepository() instanceof \Doctrine\ORM\EntityRepository);
    }

    /**
     * Test related method
     */
    public function testGetAttributeOptionRepository()
    {
        $this->assertTrue($this->manager->getAttributeOptionRepository() instanceof \Doctrine\ORM\EntityRepository);
    }

    /**
     * Test related method
     */
    public function testGetAttributeOptionValueRepository()
    {
        $this->assertTrue($this->manager->getAttributeOptionValueRepository() instanceof \Doctrine\ORM\EntityRepository);
    }

    /**
     * Test related method
     */
    public function testGetEntityValueRepository()
    {
        $this->assertTrue($this->manager->getEntityValueRepository() instanceof \Doctrine\ORM\EntityRepository);
    }

    /**
     * Test related method
     */
    public function testCreateAttribute()
    {
        $attributeType = new TextType();
        $this->assertTrue($this->manager->createAttribute($attributeType) instanceof $this->attributeClassName);
    }

    /**
     * Test related method
     */
    public function testCreateAttributeOption()
    {
        $this->assertTrue($this->manager->createAttributeOption() instanceof $this->attributeOptionClassName);
    }

    /**
     * Test related method
     */
    public function testCreateAttributeOptionValue()
    {
        $this->assertTrue($this->manager->createAttributeOptionValue() instanceof $this->attributeOptionValueClassName);
    }

    /**
     * Test related method
     */
    public function testCreateEntity()
    {
        $this->assertTrue($this->manager->createEntity() instanceof $this->flexibleClassName);
    }

    /**
     * Test related method
     */
    public function testCreateEntityAttribute()
    {
        $this->assertTrue($this->manager->createEntityAttribute() instanceof $this->flexibleAttributeClassName);
    }

    /**
     * Test related method
     */
    public function testCreateEntityValue()
    {
        $this->assertTrue($this->manager->createEntityValue() instanceof $this->flexibleValueClassName);
    }

}