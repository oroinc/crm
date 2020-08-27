<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class MagentoTransport. Used as base class for REST and SOAP transport entities
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class MagentoTransport extends Transport
{
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
     * @var boolean
     *
     * @ORM\Column(name="is_display_order_notes", type="boolean", nullable=true, options={"default"=true})
     */
    protected $isDisplayOrderNotes;

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
     * @var array
     *
     * @ORM\Column(name="shared_guest_email_list", type="simple_array", nullable=true)
     */
    protected $sharedGuestEmailList;

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
     *
     * @return MagentoTransport
     */
    public function setExtensionVersion($extensionVersion)
    {
        $this->extensionVersion = $extensionVersion;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isSupportedExtensionVersion()
    {
        return $this->getIsExtensionInstalled()
            && version_compare($this->getExtensionVersion(), SoapTransport::REQUIRED_EXTENSION_VERSION, 'ge');
    }

    /**
     * @return boolean
     */
    public function isSupportedOrderNoteExtensionVersion()
    {
        return $this->isSupportedExtensionVersion() &&
            version_compare($this->getExtensionVersion(), SoapTransport::ORDER_NOTE_VERSION_REQUIRED, 'ge');
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
     *
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
     * @param boolean $isDisplayOrderNotes
     *
     * @return MagentoTransport
     */
    public function setIsDisplayOrderNotes($isDisplayOrderNotes)
    {
        $this->isDisplayOrderNotes = $isDisplayOrderNotes;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsDisplayOrderNotes()
    {
        return $this->isDisplayOrderNotes;
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
     *
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
     *
     * @return MagentoTransport
     */
    public function setNewsletterSubscriberSyncedToId($newsletterSubscriberSyncedToId)
    {
        $this->newsletterSubscriberSyncedToId = $newsletterSubscriberSyncedToId;

        return $this;
    }

    /**
     * @return array
     */
    public function getSharedGuestEmailList()
    {
        return $this->sharedGuestEmailList;
    }

    /**
     * @param array $sharedGuestEmailList
     *
     * @return MagentoTransport
     */
    public function setSharedGuestEmailList(array $sharedGuestEmailList)
    {
        $this->sharedGuestEmailList = $sharedGuestEmailList;

        return $this;
    }
}
