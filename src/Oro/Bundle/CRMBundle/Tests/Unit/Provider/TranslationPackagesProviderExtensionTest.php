<?php

namespace Oro\Bundle\CRMBundle\Tests\Unit\Provider;

use Oro\Bundle\CRMBundle\OroCRMBundle;
use Oro\Bundle\CRMBundle\Provider\TranslationPackagesProviderExtension;
use Oro\Bundle\TranslationBundle\Tests\Unit\Provider\TranslationPackagesProviderExtensionTestAbstract;

class TranslationPackagesProviderExtensionTest extends TranslationPackagesProviderExtensionTestAbstract
{
    /**
     * {@inheritdoc}
     */
    protected function createExtension()
    {
        return new TranslationPackagesProviderExtension();
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackagesName()
    {
        return [TranslationPackagesProviderExtension::PACKAGE_NAME];
    }

    /**
     * {@inheritdoc}
     */
    public function packagePathProvider()
    {
        return [
            [
                'path' => preg_replace('/\\\\/', DIRECTORY_SEPARATOR, sprintf('%s.php', OroCRMBundle::class))
            ]
        ];
    }
}
