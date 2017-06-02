<?php

namespace Oro\Bundle\MagentoBundle\Model\OroBridgeExtension;

/**
 * This class need to collect data from
 * OroBridge Magento extension
 */
class Config
{
    const CUSTOMER_SHARING_GLOBAL = 0;
    const CUSTOMER_SHARING_PER_WEBSITE = 1;

    /** @var string */
    protected $magentoVersion;

    /** @var string */
    protected $extensionVersion;

    /** @var string */
    protected $adminUrl;

    /** @var string */
    protected $customerScope;

    /**
     * @return bool
     */
    public function isCustomerSharingPerWebsite()
    {
        return ((int)$this->getCustomerScope() === static::CUSTOMER_SHARING_PER_WEBSITE);
    }

    /**
     * @param string $customerScope
     *
     * @return Config
     */
    public function setCustomerScope($customerScope)
    {
        $this->customerScope = $customerScope;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerScope()
    {
        return $this->customerScope;
    }

    /**
     * @param string $magentoVersion
     *
     * @return Config
     */
    public function setMagentoVersion($magentoVersion)
    {
        $this->magentoVersion = $magentoVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->magentoVersion;
    }

    /**
     * @param string $extensionVersion
     *
     * @return Config
     */
    public function setExtensionVersion($extensionVersion)
    {
        $this->extensionVersion = $extensionVersion;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtensionVersion()
    {
        return $this->extensionVersion;
    }

    /**
     * @param string $adminUrl
     *
     * @return Config
     */
    public function setAdminUrl($adminUrl)
    {
        $this->adminUrl = $adminUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminUrl()
    {
        return $this->adminUrl;
    }
}
