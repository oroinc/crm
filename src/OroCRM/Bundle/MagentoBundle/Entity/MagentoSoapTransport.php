<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\IntegrationBundle\Entity\Transport;

/**
 * Class MagentoSoapTransport
 *
 * @package OroCRM\Bundle\MagentoBundle\Entity
 * @ORM\Entity
 */
class MagentoSoapTransport extends Transport
{
    /**
     * @var string
     *
     * @ORM\Column(name="wsdl_url", type="string", length=255, nullable=false)
     */
    protected $wsdlUrl;

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
     * @ORM\Column(type="date")
     */
    protected $syncStartDate;

    /**
     * @var \DateInterval
     *
     * @ORM\Column(type="string", length=50)
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

    public function __construct()
    {
        $this->setSyncStartDate(new \DateTime('2007-01-01', new \DateTimeZone('UTC')));
    }

    /**
     * @param string $wsdlUrl
     *
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        return new ParameterBag(
            [
                'api_user'       => $this->getApiUser(),
                'api_key'        => $this->getApiKey(),
                'wsdl_url'       => $this->getWsdlUrl(),
                'sync_range'     => $this->getSyncRange(),
                'website_id'     => $this->getWebsites(),
                'last_sync_date' => $this->getSyncStartDate(),
            ]
        );
    }
}
