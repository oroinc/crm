<?php

namespace Oro\Bundle\ChannelBundle;

use Oro\Bundle\ChannelBundle\Async\Topics;
use Oro\Bundle\MessageQueueBundle\DependencyInjection\Compiler\AddTopicMetaPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The ChannelBundle bundle class.
 */
class OroChannelBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $addTopicMetaPass = AddTopicMetaPass::create();
        $addTopicMetaPass
            ->add(Topics::CHANNEL_STATUS_CHANGED)
            ->add(Topics::AGGREGATE_LIFETIME_AVERAGE)
        ;

        $container->addCompilerPass($addTopicMetaPass);
    }
}
