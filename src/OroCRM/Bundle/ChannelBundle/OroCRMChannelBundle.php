<?php

namespace OroCRM\Bundle\ChannelBundle;

use Oro\Bundle\MessageQueueBundle\DependencyInjection\Compiler\AddTopicMetaPass;
use OroCRM\Bundle\ChannelBundle\Async\Topics;
use OroCRM\Bundle\ChannelBundle\DependencyInjection\CompilerPass\SettingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroCRMChannelBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new SettingsPass());

        $addTopicMetaPass = AddTopicMetaPass::create();
        $addTopicMetaPass
            ->add(Topics::CHANNEL_STATUS_CHANGED)
            ->add(Topics::AGGREGATE_LIFETIME_AVERAGE)
        ;

        $container->addCompilerPass($addTopicMetaPass);
    }
}
