<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

/**
 * Class MagentoSoapTransport
 *
 * @package OroCRM\Bundle\MagentoBundle\Entity
 * @ORM\Entity(repositoryClass="OroCRM\Bundle\MagentoBundle\Entity\Repository\MagentoSoapTransportRepository")
 * @Oro\Loggable()
 */
class MagentoSoapTransport extends Transport
{
    /**
     * @var string
     *
     * @ORM\Column(name="wsdl_url", type="string", length=255, nullable=false)
     * @Oro\Versioned()
     */
    protected $wsdlUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="api_user", type="string", length=255, nullable=false)
     * @Oro\Versioned()
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
     * @Oro\Versioned()
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
     * @Oro\Versioned()
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
     * @Oro\Versioned()
     */
    protected $extensionVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="magento_version", type="string", length=255, nullable=true)
     * @Oro\Versioned()
     */
    protected $magentoVersion;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_wsi_mode", type="boolean")
     */
    protected $isWsiMode = false;

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
     * @Oro\Versioned()
     */
    protected $adminUrl;

    /**
     * @var int
     *
     * @ORM\Column(name="mage_newsl_subscr_synced_to_id", type="integer", nullable=true)
     */
    protected $newsletterSubscriberSyncedToId;

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
     * @param string $wsdlUrl
     *
     * @return MagentoSoapTransport
     */
    public function setWsdlUrl($wsdlUrl)
    {
        $this->wsdlUrl = $wsdlUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getWsdlUrl()
    {
        return $this->wsdlUrl;
    }

    /**
     * @param string $apiUser
     *
     * @return MagentoSoapTransport
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
     * @param string $apiKey
     *
     * @return MagentoSoapTransport
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
     * @return MagentoSoapTransport
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
     * @return MagentoSoapTransport
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
     * @return MagentoSoapTransport
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
     * @return MagentoSoapTransport
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
     * @return MagentoSoapTransport
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
     * @return MagentoSoapTransport
     */
    public function setExtensionVersion($extensionVersion)
    {
        $this->extensionVersion = $extensionVersion;

        return $this;
    }

    /**
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
     * @return MagentoSoapTransport
     */
    public function setMagentoVersion($magentoVersion)
    {
        $this->magentoVersion = $magentoVersion;

        return $this;
    }

    /**
     * @param boolean $isWsiMode
     *
     * @return MagentoSoapTransport
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

    /**
     * @param boolean $guestCustomerSync
     *
     * @return MagentoSoapTransport
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
                    'wsdl_url' => $this->getWsdlPath(),
                    'sync_range' => $this->getSyncRange(),
                    'wsi_mode' => $this->getIsWsiMode(),
                    'guest_customer_sync' => $this->getGuestCustomerSync(),
                    'website_id' => $this->getWebsiteId(),
                    'start_sync_date' => $this->getSyncStartDate(),
                    'initial_sync_start_date' => $this->getInitialSyncStartDate(),
                    'extension_version' => $this->getExtensionVersion(),
                    'magento_version' => $this->getMagentoVersion(),
                    'newsletter_subscriber_synced_to_id' => $this->getNewsletterSubscriberSyncedToId()
                ]
            );
        }

        return $this->settings;
    }

    /**
     * @param string $adminUrl
     *
     * @return MagentoSoapTransport
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
     * @return MagentoSoapTransport
     */
    public function setInitialSyncStartDate($initialSyncStartDate)
    {
        $this->initialSyncStartDate = $initialSyncStartDate;

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

        return $this->getWsdlUrl();
    }

    /**
     * @param string $wsdlCachePath
     * @return MagentoSoapTransport
     */
    public function setWsdlCachePath($wsdlCachePath)
    {
        $this->wsdlCachePath = $wsdlCachePath;
        $this->settings = null;

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
     * @return MagentoSoapTransport
     */
    public function setNewsletterSubscriberSyncedToId($newsletterSubscriberSyncedToId)
    {
        $this->newsletterSubscriberSyncedToId = $newsletterSubscriberSyncedToId;

        return $this;
    }
}
