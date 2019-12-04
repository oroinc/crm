<?php

namespace Oro\Bridge\CallCRM\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('oro_crm_call');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
