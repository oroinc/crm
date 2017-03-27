<?php

namespace Oro\Bundle\CRMBundle\Tests\Functional\Provider;

use Oro\Bundle\TranslationBundle\Tests\Functional\Provider\TranslationPackagesProviderExtensionTestAbstract;

class TranslationPackagesProviderExtensionTest extends TranslationPackagesProviderExtensionTestAbstract
{
    /**
     * {@inheritdoc}
     */
    public function expectedPackagesDataProvider()
    {
        yield 'OroCRM Package' => [
            'packageName' => 'OroCRM',
            'fileToLocate' => 'Oro/Bundle/CRMBundle/OroCRMBundle.php'
        ];
    }
}
