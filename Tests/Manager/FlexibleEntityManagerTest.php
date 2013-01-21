<?php
namespace Oro\Bundle\FlexibleEntityBundle\Test\Manager;

use Doctrine\ORM\EntityManager;

use Doctrine\Tests\OrmTestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Symfony\Component\DependencyInjection\Container;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleEntityManager;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class FlexibleEntityManagerTest extends OrmTestCase
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var FlexibleEntityManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $attributeClassName;

    /**
     * @var string
     */
    protected $attributeOptionClassName;

    /**
     * @var string
     */
    protected $attributeOptionValueClassName;

    /**
     * @var string
     */
    protected $flexibleClassName;

    /**
     * @var string
     */
    protected $flexibleAttributeClassName;

    /**
     * @var string
     */
    protected $flexibleValueClassName;

    /**
     * @var string
     */
    protected $defaultScope;

    /**
     * @var string
     */
    protected $defaultLocale;

    /**
     * @var array
     */
    protected $flexibleConfig;

    /**
     * Set up unit test
     */
    public function setUp()
    {
        // config
        $this->attributeClassName            = 'Oro\Bundle\FlexibleEntityBundle\Entity\Attribute';
        $this->attributeOptionClassName      = 'Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption';
        $this->attributeOptionValueClassName = 'Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue';
        $this->flexibleClassName             = 'Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Flexible';
        $this->flexibleValueClassName        = 'Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\FlexibleValue';
        $this->flexibleAttributeClassName    = 'Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\FlexibleAttribute';

        $this->defaultLocale          = 'en_US';
        $this->defaultScope           = 'mobile';
        $this->flexibleConfig = array(
            'entities_config' => array(
                    $this->flexibleClassName => array(
                            'flexible_manager'                      => 'demo_manager',
                            'flexible_entity_class'                 => $this->flexibleClassName,
                            'flexible_entity_value_class'           => $this->flexibleValueClassName,
                            'flexible_attribute_extended_class'     => $this->flexibleAttributeClassName,
                            'flexible_attribute_class'              => $this->attributeClassName,
                            'flexible_attribute_option_class'       => $this->attributeOptionClassName,
                            'flexible_attribute_option_value_class' => $this->attributeOptionValueClassName,
                            'default_locale'                        => $this->defaultLocale,
                            'default_scope'                         => $this->defaultScope
                    )
            )
        );
        // prepare test entity manager
        $reader = new AnnotationReader();
        $metadataDriver = new AnnotationDriver($reader, 'Oro\\Bundle\\FlexibleEntityBundle\\Test\\Entity\\Demo');
        $this->entityManager = $this->_getTestEntityManager();
        $this->entityManager->getConfiguration()->setMetadataDriverImpl($metadataDriver);
        // prepare test container
        $this->container = new Container();
        $this->container->set('doctrine.orm.entity_manager', $this->entityManager);
        $this->container->setParameter('oro_flexibleentity.entities_config', $this->flexibleConfig);
        // prepare simple entity manager (use default entity manager)
        $this->manager = new FlexibleEntityManager($this->container, $this->flexibleClassName);
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
        $this->assertTrue($this->manager->createAttribute() instanceof $this->attributeClassName);
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