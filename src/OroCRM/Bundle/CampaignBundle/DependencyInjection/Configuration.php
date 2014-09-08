<?php

namespace OroCRM\Bundle\CampaignBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('oro_crm_campaign');

        SettingsBuilder::append(
            $rootNode,
            [
                'campaign_from_email' => ['value' => sprintf('no-reply@%s.example', gethostname())]
            ]
        );

        return $treeBuilder;
    }
}
