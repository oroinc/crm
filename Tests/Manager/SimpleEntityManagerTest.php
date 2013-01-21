<?php
namespace Oro\Bundle\FlexibleEntityBundle\Test\Manager;

use Doctrine\ORM\EntityManager;

use Doctrine\Tests\OrmTestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Symfony\Component\DependencyInjection\Container;
use Oro\Bundle\FlexibleEntityBundle\Manager\SimpleEntityManager;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class SimpleEntityManagerTest extends OrmTestCase
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
     * @var SimpleEntityManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * Set up unit test
     */
    public function setUp()
    {
        // prepare test entity manager
        $reader = new AnnotationReader();
        $metadataDriver = new AnnotationDriver($reader, 'Oro\\Bundle\\FlexibleEntityBundle\\Test\\Entity\\Demo');
        $this->entityManager = $this->_getTestEntityManager();
        $this->entityManager->getConfiguration()->setMetadataDriverImpl($metadataDriver);
        // prepare test container
        $this->container = new Container();
        $this->container->set('doctrine.orm.entity_manager', $this->entityManager);
        // prepare simple entity manager (use default entity manager)
        $this->entityName = 'Oro\Bundle\FlexibleEntityBundle\Tests\Entity\Demo\Simple';
        $this->manager = new SimpleEntityManager($this->container, $this->entityName);
    }

    /**
     * test related method
     */
    public function testConstructWithCustomEntityManager()
    {
        $myManager = new SimpleEntityManager($this->container, $this->entityName, $this->entityManager);
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
    public function testGetEntityName()
    {
        $this->assertEquals($this->manager->getEntityName(), $this->entityName);
    }

    /**
     * test related method
     */
    public function testGetEntityRepository()
    {
        $this->assertTrue($this->manager->getEntityRepository() instanceof \Doctrine\ORM\EntityRepository);
    }

    /**
     * test related method
     */
    public function testCreateEntity()
    {
        $this->assertTrue($this->manager->createEntity() instanceof $this->entityName);
    }

}