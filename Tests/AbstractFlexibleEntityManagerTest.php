<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests;

use Doctrine\Tests\OrmTestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Symfony\Component\DependencyInjection\Container;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
abstract class AbstractFlexibleManagerTest extends AbstractOrmTest
{

    /**
     * @var FlexibleManager
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
        parent::setUp();
        // flexible entity manager configuration
        $this->attributeClassName            = 'Oro\Bundle\FlexibleEntityBundle\Entity\Attribute';
        $this->attributeOptionClassName      = 'Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption';
        $this->attributeOptionValueClassName = 'Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue';
        $this->flexibleClassName             = 'Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Flexible';
        $this->flexibleValueClassName        = 'Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\FlexibleValue';
        $this->flexibleAttributeClassName    = 'Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\FlexibleAttribute';
        $this->defaultLocale                 = 'en_US';
        $this->defaultScope                  = 'mobile';
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
        // prepare test container
        $this->container->setParameter('oro_flexibleentity.flexible_config', $this->flexibleConfig);
        // prepare simple entity manager (use default entity manager)
        $this->manager = new FlexibleManager($this->container, $this->flexibleClassName);
        $this->container->set('demo_manager', $this->manager);
        // mock global event dispatcher 'event_dispatcher'
        $dispatcher = new EventDispatcher();
        $this->container->set('event_dispatcher', $dispatcher);
    }

}