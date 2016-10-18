<?php

namespace Oro\Bundle\MagentoBundle;

use Oro\Bundle\MessageQueueBundle\DependencyInjection\Compiler\AddTopicMetaPass;
use OroCRM\Bundle\MagentoBundle\Async\Topics;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroMagentoBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $addTopicPass = AddTopicMetaPass::create()
            ->add(Topics::SYNC_CART_EXPIRATION_INTEGRATION)
            ->add(Topics::SYNC_INITIAL_INTEGRATION)
        ;
        $container->addCompilerPass($addTopicPass);
    }
}
