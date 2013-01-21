<?php
namespace Oro\Bundle\FlexibleEntityBundle\Tests\Listener;

use Oro\Bundle\FlexibleEntityBundle\Tests\AbstractOrmTest;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\FlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Listener\HasDefaultValueListener;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class HasDefaultValueListenerTest extends AbstractOrmTest
{
    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @var FlexibleValue
     */
    protected $value;

    /**
     * @var HasDefaultValueListener
     */
    protected $listener;

    /**
     * Default value set for a flexible value not set
     * @var string
     */
    protected $defaultValue = 'default-value';

    /**
     * Define value set in a flexible value
     * @var string
     */
    protected $definedValue = 'defined-value';

    /**
     * Set up unit test
     */
    public function setUp()
    {
        parent::setUp();
        // create attribute
        $this->attribute = new Attribute();
        $this->attribute->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
        $this->attribute->setDefaultValue($this->defaultValue);

        $this->value = new FlexibleValue();
        $this->value->setAttribute($this->attribute);

        // create listener
        $this->listener = new HasDefaultValueListener();
    }

    /**
     * test related method
     */
    public function testGetSubscribedEvents()
    {
        $events = array('prePersist', 'preUpdate');
        $this->assertEquals($this->listener->getSubscribedEvents(), $events);
    }

    /**
     * test related method
     */
    public function testPrePersist()
    {
        // check before
        $this->assertNull($this->value->getData());

        // call method
        $args = new LifecycleEventArgs($this->value, $this->entityManager);
        $this->listener->prePersist($args);

        // assertions
        $this->assertNotNull($this->value->getData());
        $this->assertEquals($this->defaultValue, $this->value->getData());


        // change value
        $this->value->setData($this->definedValue);
        $this->assertNotEquals($this->defaultValue, $this->value->getData());

        // call method
        $args = new LifecycleEventArgs($this->value, $this->entityManager);
        $this->listener->prePersist($args);

        // assertions
        $this->assertNotNull($this->value->getData());
        $this->assertEquals($this->definedValue, $this->value->getData());
    }

    /**
     * Test related method
     */
    public function testPreUpdate()
    {
        // check before
        $this->assertNull($this->value->getData());

        // call method
        $args = new LifecycleEventArgs($this->value, $this->entityManager);
        $this->listener->preUpdate($args);

        // assertions
        $this->assertNotNull($this->value->getData());
        $this->assertEquals($this->defaultValue, $this->value->getData());


        // change value
        $this->value->setData($this->definedValue);
        $this->assertNotEquals($this->defaultValue, $this->value->getData());

        // call method
        $args = new LifecycleEventArgs($this->value, $this->entityManager);
        $this->listener->preUpdate($args);

        // assertions
        $this->assertNotNull($this->value->getData());
        $this->assertEquals($this->definedValue, $this->value->getData());
    }

}