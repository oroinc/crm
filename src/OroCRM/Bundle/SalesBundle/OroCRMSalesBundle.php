<?php

namespace OroCRM\Bundle\SalesBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\WorkflowBundle\DependencyInjection\Compiler\AddViewAttributeTwigTemplateCompilerPass;

class OroCRMSalesBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new AddViewAttributeTwigTemplateCompilerPass('OroCRMSalesBundle:Workflow:view_attributes.html.twig')
        );
    }
}
