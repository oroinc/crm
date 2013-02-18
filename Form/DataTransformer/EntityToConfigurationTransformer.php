<?php
namespace Oro\Bundle\DataFlowBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\DataFlowBundle\Entity\Configuration;
use Oro\Bundle\DataFlowBundle\Configuration\ConfigurationInterface;

/**
 * Transform configuration to entity and reverse operation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class EntityToConfigurationTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * Format used for serialization
     * @var string
     */
    protected $format;

    /**
     * @param ObjectManager $om     object manager
     * @param string        $format format
     */
    public function __construct(ObjectManager $om, $format = 'json')
    {
        $this->om = $om;
        $this->format = $format;
    }

    /**
     * Transforms an object (entity) to a object (configuration).
     *
     * @param Configuration $entity
     *
     * @return ConfigurationInterface
     */
    public function transform($entity)
    {
        if (!$entity) {
            return null;
        }

        $configuration = $entity->deserialize();
        $configuration->setId($entity->getId());

        return $configuration;
    }

    /**
     * Transforms a configuration to an entity.
     *
     * @param ConfigurationInterface $configuration
     *
     * @return Configuration
     */
    public function reverseTransform($configuration)
    {
        // get / create entity
        if ($configuration->getId()) {
            $repository = $this->om->getRepository('OroDataFlowBundle:Configuration');
            $entity = $repository->find($configuration->getId());
        } else {
            $entity = new Configuration();
        }

        // serialize
        $entity->setTypeName(get_class($configuration));
        $entity->setFormat($this->format);
        $entity->serialize($configuration);

        return $entity;
    }
}
