<?php

namespace Oro\Bundle\ContactUsBundle;

use Oro\Bundle\LocaleBundle\DependencyInjection\Compiler\DefaultFallbackExtensionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The ContactUsBundle bundle class.
 */
class OroContactUsBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DefaultFallbackExtensionPass([
            'Oro\Bundle\ContactUsBundle\Entity\ContactReason' => [
                'title' => 'titles'
            ]
        ]));
    }
}
