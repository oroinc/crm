<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Form\DataTransformer;

use Oro\Bundle\DataFlowBundle\Form\DataTransformer\EntityToConfigurationTransformer;
use Doctrine\Tests\OrmTestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Oro\Bundle\DataFlowBundle\Entity\Configuration;
use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;
use Oro\Bundle\DataFlowBundle\Tests\Configuration\Demo\MyConfiguration;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class EntityToConfigurationTransformerTest extends OrmTestCase
{

    /**
     * @var EntityToConfigurationTransformer
     */
    protected $transformer;

    /**
     * Setup
     */
    public function setup()
    {
        // prepare test entity manager
        $entityPath = 'Oro\\Bundle\\DataFlowBundle\\Test\\Entity\\Demo';
        $reader = new AnnotationReader();
        $metadataDriver = new AnnotationDriver($reader, $entityPath);
        $entityManager = $this->_getTestEntityManager();
        $entityManager->getConfiguration()->setMetadataDriverImpl($metadataDriver);

        $this->transformer = new EntityToConfigurationTransformer($entityManager, 'xml');
    }

    /**
     * Test related method
     */
    public function testTransform()
    {
        $entity = new Configuration();
        $entity->setTypeName('Oro\Bundle\DataFlowBundle\Tests\Configuration\Demo\MyConfiguration');
        $configuration = $this->transformer->transform($entity);
        $this->assertTrue($configuration instanceof ConfigurationInterface);
    }

    /**
     * Test related method
     */
    public function testReverseTransform()
    {
        $configuration = new MyConfiguration();
        $entity = $this->transformer->reverseTransform($configuration);
        $this->assertTrue($entity instanceof Configuration);
    }
}
