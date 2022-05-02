<?php

namespace Oro\Bundle\ContactBundle;

use Oro\Bundle\ContactBundle\DependencyInjection\Compiler\EmailHolderHelperConfigPass;
use Oro\Bundle\ContactBundle\DependencyInjection\Compiler\PhoneProviderConfigPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroContactBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EmailHolderHelperConfigPass());
        $container->addCompilerPass(new PhoneProviderConfigPass());
    }
}
