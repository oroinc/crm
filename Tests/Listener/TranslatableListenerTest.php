<?php
namespace Oro\Bundle\FlexibleEntityBundle\Test\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\Tests\OrmTestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Symfony\Component\DependencyInjection\Container;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleEntityManager;
use Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Flexible;
use Oro\Bundle\FlexibleEntityBundle\Listener\TranslatableListener;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class TranslatableListenerTest extends OrmTestCase
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
     * @var Flexible
     */
    protected $flexible;

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
        $this->container->set('demo_manager', $this->manager);
        // create listener
        $this->listener = new TranslatableListener();
        $this->listener->setContainer($this->container);
        // create flexible entity
        $this->flexible = new Flexible();
    }

    /**
     * test related method
     */
    public function testGetSubscribedEvents()
    {
        $events = array('postLoad');
        $this->assertEquals($this->listener->getSubscribedEvents(), $events);
    }

    /**
     * test related method
     */
    public function testPostLoad()
    {
        // check before
        $this->assertNull($this->flexible->getLocale());
        // call method
        $args = new LifecycleEventArgs($this->flexible, $this->entityManager);
        $this->listener->postLoad($args);
        // check after (locale is setup)
        $this->assertEquals($this->flexible->getLocale(), $this->defaultLocale);
        // change locale from manager, and re-call
        $code = 'it_IT';
        $this->manager->setLocale($code);
        $this->listener->postLoad($args);
        //locale heas been changed by post load
        $this->assertEquals($this->flexible->getLocale(), $code);
    }

}