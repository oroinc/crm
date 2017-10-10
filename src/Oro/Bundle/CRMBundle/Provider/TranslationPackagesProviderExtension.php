<?php

namespace Oro\Bundle\CRMBundle\Provider;

use Symfony\Component\Config\FileLocator;

use Oro\Bundle\TranslationBundle\Provider\TranslationPackagesProviderExtensionInterface;

class TranslationPackagesProviderExtension implements TranslationPackagesProviderExtensionInterface
{
    const PACKAGE_NAME = 'OroCRM';

    /**
     * @var string
     */
    private $rootDirectory;

    /**
     * @param string $rootDirectory
     */
    public function __construct($rootDirectory)
    {
        $this->rootDirectory = $rootDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageNames()
    {
        return [self::PACKAGE_NAME];
    }

    /**
     * {@inheritdoc}
     */
    public function getPackagePaths()
    {
        return new FileLocator([
            $this->rootDirectory . '/../vendor/oro/crm/src',
            $this->rootDirectory . '/../vendor/oro/crm-call-bundle/src/src',
            $this->rootDirectory . '/../vendor/oro/crm-dotmailer/src',
            $this->rootDirectory . '/../vendor/oro/crm-hangouts-call-bundle/src',
            $this->rootDirectory . '/../vendor/oro/crm-abandoned-cart/src',
            $this->rootDirectory . '/../vendor/oro/crm-magento-embedded-contact-us/src',
            $this->rootDirectory . '/../vendor/oro/crm-mail-chimp/src',
            $this->rootDirectory . '/../vendor/oro/marketing/src',
            $this->rootDirectory . '/../vendor/oro/crm-task-bundle/src',
            $this->rootDirectory . '/../vendor/oro/crm-zendesk/src',
        ]);
    }
}
