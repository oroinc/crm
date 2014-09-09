<?php

namespace OroCRM\Bundle\CampaignBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use OroCRM\Bundle\CampaignBundle\DependencyInjection\Compiler\TransportPass;

class OroCRMCampaignBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TransportPass());
    }
}
