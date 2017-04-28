<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

/**
 * Class MagentoTransport
 *
 * @package Oro\Bundle\MagentoBundle\Entity
 * @ORM\Entity(repositoryClass="Oro\Bundle\MagentoBundle\Entity\Repository\MagentoTransportRepository")
 */
class MagentoTransport extends Transport
{
    /**
     * @var string
     *
     * @ORM\Column(name="api_token", type="string", length=255, nullable=false)
     */
    protected $apiToken;

    /**
     * @var string
     *
     * @ORM\Column(name="api_url", type="string", length=255, nullable=false)
     */
    protected $apiUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="api_user", type="string", length=255, nullable=false)
     */
    protected $apiUser;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key", type="string", length=255, nullable=false)
     */
    protected $apiKey;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sync_start_date", type="date")
     */
    protected $syncStartDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="initial_sync_start_date", type="datetime", nullable=true)
     */
    protected $initialSyncStartDate;

    /**
     * @var \DateInterval
     *
     * @ORM\Column(name="sync_range", type="string", length=50)
     */
    protected $syncRange;

    /**
     * @var int
     *
     * @ORM\Column(name="website_id", type="integer", nullable=true)
     */
    protected $websiteId = null;

    /**
     * @var array
     *
     * @ORM\Column(name="websites", type="array")
     */
    protected $websites = [];

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_extension_installed", type="boolean")
     */
    protected $isExtensionInstalled = false;

    /**
     * @var string
     *
     * @ORM\Column(name="extension_version", type="string", length=255, nullable=true)
     */
    protected $extensionVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="magento_version", type="string", length=255, nullable=true)
     */
    protected $magentoVersion;

    /**
     * @var boolean
     *
     * @ORM\Column(name="guest_customer_sync", type="boolean")
     */
    protected $guestCustomerSync = true;

    /**
     * @var string
     *
     * @ORM\Column(name="admin_url", type="string", length=255, nullable=true)
     */
    protected $adminUrl;

    /**
     * @var int
     *
     * @ORM\Column(name="mage_newsl_subscr_synced_to_id", type="integer", nullable=true)
     */
    protected $newsletterSubscriberSyncedToId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_wsi_mode", type="boolean")
     */
    protected $isWsiMode = false;

    /**
     * @var string
     */
    protected $wsdlCachePath;

    /**
     * @var ParameterBag
     */
    protected $settings;

    public function __construct()
    {
        $this->setSyncStartDate(new \DateTime('2007-01-01', new \DateTimeZone('UTC')));
    }

    /**
     * @param string $apiUrl
     *
     * @return MagentoTransport
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param string $apiUser
     *
     * @return MagentoTransport
     */
    public function setApiUser($apiUser)
    {
        $this->apiUser = $apiUser;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiUser()
    {
        return $this->apiUser;
    }

    /**
     * @param string $apiToken
     *
     * @return MagentoTransport
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * @param string $apiKey
     *
     * @return MagentoTransport
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param \DateTime $syncStartDate
     *
     * @return MagentoTransport
     */
    public function setSyncStartDate(\DateTime $syncStartDate = null)
    {
        $this->syncStartDate = $syncStartDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSyncStartDate()
    {
        return $this->syncStartDate;
    }

    /**
     * @param \DateInterval $syncRange
     *
     * @return MagentoTransport
     */
    public function setSyncRange($syncRange)
    {
        $this->syncRange = $syncRange;

        return $this;
    }

    /**
     * @return \DateInterval
     */
    public function getSyncRange()
    {
        return $this->syncRange;
    }

    /**
     * @param int $websiteId
     *
     * @return MagentoTransport
     */
    public function setWebsiteId($websiteId)
    {
        $this->websiteId = $websiteId;

        return $this;
    }

    /**
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->websiteId;
    }

    /**
     * @param array $websites
     *
     * @return MagentoTransport
     */
    public function setWebsites(array $websites)
    {
        $this->websites = $websites;

        return $this;
    }

    /**
     * @return array
     */
    public function getWebsites()
    {
        return $this->websites;
    }

    /**
     * @param boolean $isExtensionInstalled
     *
     * @return MagentoTransport
     */
    public function setIsExtensionInstalled($isExtensionInstalled)
    {
        $this->isExtensionInstalled = $isExtensionInstalled;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsExtensionInstalled()
    {
        return $this->isExtensionInstalled;
    }

    /**
     * @return string
     */
    public function getExtensionVersion()
    {
        return $this->extensionVersion;
    }

    /**
     * @param string $extensionVersion
     * @return MagentoTransport
     */
    public function setExtensionVersion($extensionVersion)
    {
        $this->extensionVersion = $extensionVersion;

        return $this;
    }

    /**
     * TODO: move required version to another class
     * @return bool
     */
    public function isSupportedExtensionVersion()
    {
        return $this->getIsExtensionInstalled()
            && version_compare($this->getExtensionVersion(), SoapTransport::REQUIRED_EXTENSION_VERSION, 'ge');
    }

    /**
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->magentoVersion;
    }

    /**
     * @param string $magentoVersion
     * @return MagentoTransport
     */
    public function setMagentoVersion($magentoVersion)
    {
        $this->magentoVersion = $magentoVersion;

        return $this;
    }

    /**
     * @param boolean $guestCustomerSync
     *
     * @return MagentoTransport
     */
    public function setGuestCustomerSync($guestCustomerSync)
    {
        $this->guestCustomerSync = $guestCustomerSync;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getGuestCustomerSync()
    {
        return $this->guestCustomerSync;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                [
                    'api_user' => $this->getApiUser(),
                    'api_key' => $this->getApiKey(),
                    'api_url' => $this->getApiUrl(),
                    'sync_range' => $this->getSyncRange(),
                    'guest_customer_sync' => $this->getGuestCustomerSync(),
                    'website_id' => $this->getWebsiteId(),
                    'start_sync_date' => $this->getSyncStartDate(),
                    'initial_sync_start_date' => $this->getInitialSyncStartDate(),
                    'extension_version' => $this->getExtensionVersion(),
                    'magento_version' => $this->getMagentoVersion(),
                    'newsletter_subscriber_synced_to_id' => $this->getNewsletterSubscriberSyncedToId(),
                    'wsdl_url' => $this->getWsdlPath(),
                    'wsi_mode' => $this->getIsWsiMode(),
                    'api_token' => $this->getApiToken(),
                ]
            );
        }

        return $this->settings;
    }

    /**
     * @param string $adminUrl
     *
     * @return MagentoTransport
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

    /**
     * @return \DateTime
     */
    public function getInitialSyncStartDate()
    {
        return $this->initialSyncStartDate;
    }

    /**
     * @param \DateTime $initialSyncStartDate
     * @return MagentoTransport
     */
    public function setInitialSyncStartDate($initialSyncStartDate)
    {
        $this->initialSyncStartDate = $initialSyncStartDate;

        return $this;
    }

    /**
     * @return int
     */
    public function getNewsletterSubscriberSyncedToId()
    {
        return $this->newsletterSubscriberSyncedToId;
    }

    /**
     * @param int $newsletterSubscriberSyncedToId
     * @return MagentoTransport
     */
    public function setNewsletterSubscriberSyncedToId($newsletterSubscriberSyncedToId)
    {
        $this->newsletterSubscriberSyncedToId = $newsletterSubscriberSyncedToId;

        return $this;
    }

    /**
     * @return string
     */
    protected function getWsdlPath()
    {
        if ($this->wsdlCachePath) {
            return $this->wsdlCachePath;
        }

        return $this->getApiUrl();
    }

    /**
     * @deprecated since 2.3 version. Use getApiUrl() instead
     * @return string
     */
    public function getWsdlUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @deprecated since 2.3 version. Use setApiUrl() instead
     * @param $wsdlUrl
     * @return MagentoTransport
     */
    public function setWsdlUrl($wsdlUrl)
    {
        $this->apiUrl = $wsdlUrl;

        return $this;
    }

    /**
     * @param string $wsdlCachePath
     * @return MagentoTransport
     */
    public function setWsdlCachePath($wsdlCachePath)
    {
        $this->wsdlCachePath = $wsdlCachePath;
        $this->settings = null;

        return $this;
    }

    /**
     * @param boolean $isWsiMode
     *
     * @return MagentoTransport
     */
    public function setIsWsiMode($isWsiMode)
    {
        $this->isWsiMode = $isWsiMode;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsWsiMode()
    {
        return $this->isWsiMode;
    }
}
