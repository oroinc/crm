<?php

namespace Oro\Bundle\ContactUsBundle;

use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\LocaleBundle\DependencyInjection\Compiler\DefaultFallbackExtensionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Add localizable titles to ContactReason entity
 */
class OroContactUsBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new DefaultFallbackExtensionPass([
                ContactReason::class => [
                    'title' => 'titles'
                ]
            ]));
    }
}
