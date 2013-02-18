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
class ConfigurationToEntityTransformer implements DataTransformerInterface
{

    /**
     * Transforms an object (entity) to a object (configuration).
     *
     * @param  Configuration $entity
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
     * @param  ConfigurationInterface $configuration
     *
     * @return Configuration
     */
    public function reverseTransform($configuration)
    {
        if (!$configuration) {
            return null;
        }

        $entity = new Configuration();
        $entity->setTypeName(get_class($configuration));
        $entity->serialize($configuration);
//        $entity->setId($configuration->getId());

//        var_dump($entity); exit();

        return $entity;
    }
}