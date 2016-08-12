<?php

namespace OroCRM\Bundle\AnalyticsBundle;

use Oro\Bundle\MessageQueueBundle\DependencyInjection\Compiler\AddTopicMetaPass;
use OroCRM\Bundle\AnalyticsBundle\Async\Topics;
use OroCRM\Bundle\AnalyticsBundle\DependencyInjection\CompilerPass\AnalyticsBuilderPass;
use OroCRM\Bundle\AnalyticsBundle\DependencyInjection\CompilerPass\RFMBuilderPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroCRMAnalyticsBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new AnalyticsBuilderPass())
            ->addCompilerPass(new RFMBuilderPass());

        $addTopicPass = AddTopicMetaPass::create()
            ->add(Topics::CALCULATE_CHANNEL_ANALYTICS)
            ->add(Topics::CALCULATE_ALL_CHANNELS_ANALYTICS)
        ;
        $container->addCompilerPass($addTopicPass);
    }
}
