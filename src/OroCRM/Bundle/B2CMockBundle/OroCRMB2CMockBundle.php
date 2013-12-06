<?php

namespace OroCRM\Bundle\B2CMockBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler\AddViewAttributeTwigTemplateCompilerPass;

class OroCRMB2CMockBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new AddViewAttributeTwigTemplateCompilerPass('OroCRMB2CMockBundle:Workflow:view_attributes.html.twig')
        );
    }
}
