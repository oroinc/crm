<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

trait ExtensionVersionTrait
{
    /**
     * Check that version is supported
     *
     * @param string $version
     *
     * @return bool
     */
    protected function isSupportedVersion($version)
    {
        return $this->isExtensionInstalled() && $this->compareExtensionVersion($version);
    }

    /**
     * Compare version with supported version which defined in cons REQUIRED_EXTENSION_VERSION
     *
     * @param string $version
     *
     * @return bool
     */
    protected function compareExtensionVersion($version)
    {
        return version_compare($version, static::REQUIRED_EXTENSION_VERSION, 'ge');
    }
}
