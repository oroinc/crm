<?php

namespace OroCRM\Bundle\MagentoBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler\AddViewAttributeTwigTemplateCompilerPass;

class OroCRMMagentoBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new AddViewAttributeTwigTemplateCompilerPass('OroCRMMagentoBundle:Workflow:view_attributes.html.twig')
        );
    }
}
