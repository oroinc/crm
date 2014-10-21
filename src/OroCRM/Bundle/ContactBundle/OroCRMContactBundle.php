<?php

namespace OroCRM\Bundle\ContactBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use OroCRM\Bundle\ContactBundle\DependencyInjection\Compiler\EmailHolderHelperConfigPass;
use OroCRM\Bundle\ContactBundle\DependencyInjection\Compiler\PhoneProviderConfigPass;

class OroCRMContactBundle extends Bundle
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
