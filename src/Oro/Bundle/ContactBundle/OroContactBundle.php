<?php

namespace Oro\Bundle\ContactBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\ContactBundle\DependencyInjection\Compiler\EmailHolderHelperConfigPass;
use Oro\Bundle\ContactBundle\DependencyInjection\Compiler\PhoneProviderConfigPass;

class OroContactBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EmailHolderHelperConfigPass());
        $container->addCompilerPass(new PhoneProviderConfigPass());
    }
}
