<?php
namespace Oro\Bundle\FlexibleEntityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Flexible entity configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oro_flexibleentity');

        $rootNode->children()

            ->append($this->addEntityNode())

        ->end();

        return $treeBuilder;
    }

    /**
     * Return flexible entity configuration
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    public function addEntityNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('entities_config');

        $node
            ->prototype('array')
            ->children()

                // required to setup a minimal flexible entity
                ->scalarNode('flexible_manager')
                ->isRequired()
                ->end()

                ->scalarNode('flexible_entity_class')
                ->isRequired()
                ->end()

                ->scalarNode('flexible_entity_value_class')
                ->isRequired()
                ->end()

                // optional, to define extended flexible attribute
                ->scalarNode('flexible_attribute_extended_class')
                ->defaultValue(false)
                ->end()

                // optional, to define customized attribute and option models
                ->scalarNode('flexible_attribute_class')
                ->defaultValue('Oro\Bundle\FlexibleEntityBundle\Entity\Attribute')
                ->end()

                ->scalarNode('flexible_attribute_option_class')
                ->defaultValue('Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption')
                ->end()

                ->scalarNode('flexible_attribute_option_value_class')
                ->defaultValue('Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOptionValue')
                ->end()

                // optional, default locale used for entity values
                ->scalarNode('default_locale')
                ->defaultValue('en_US')
                ->end()

                // optional, default scope used for entity values
                ->scalarNode('default_scope')
                ->defaultValue(null)
                ->end()

                // optional, init mode for flexible (add a value for each attribute or add value for required attributes)
                ->scalarNode('flexible_init_mode')
                ->defaultValue('all_attributes')
                ->validate()
                ->ifNotInArray(array('all_attributes', 'required_attributes'))
                ->thenInvalid('Invalid flexible init mode "%s"')
                ->end()
                ->end()
            ->end()
        ->end();

        return $node;
    }
}
