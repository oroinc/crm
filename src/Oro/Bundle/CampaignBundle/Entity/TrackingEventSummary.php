<?php

namespace Oro\Bundle\CampaignBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\TrackingBundle\Entity\TrackingWebsite;

/**
 * @ORM\Table(name="orocrm_campaign_te_summary", indexes={
 *     @ORM\Index(name="tes_event_name_idx", columns={"name"}),
 *     @ORM\Index(name="tes_event_loggedAt_idx", columns={"logged_at"}),
 *     @ORM\Index(name="tes_code_idx", columns={"code"}),
 *     @ORM\Index(name="tes_visits_idx", columns={"visit_count"})
 * })
 * @ORM\Entity(repositoryClass="Oro\Bundle\CampaignBundle\Entity\Repository\TrackingEventSummaryRepository")
 */
class TrackingEventSummary
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var TrackingWebsite
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\TrackingBundle\Entity\TrackingWebsite")
     * @ORM\JoinColumn(name="website_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $website;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    protected $code;

    /**
     * @var integer
     *
     * @ORM\Column(name="visit_count", type="integer")
     */
    protected $visitCount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="logged_at", type="date")
     */
    protected $loggedAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return TrackingEventSummary
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return TrackingEventSummary
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set loggedAt
     *
     * @param \DateTime $loggedAt
     * @return TrackingEventSummary
     */
    public function setLoggedAt($loggedAt)
    {
        $this->loggedAt = $loggedAt;

        return $this;
    }

    /**
     * Get loggedAt
     *
     * @return \DateTime
     */
    public function getLoggedAt()
    {
        return $this->loggedAt;
    }

    /**
     * Set website
     *
     * @param TrackingWebsite $website
     * @return TrackingEventSummary
     */
    public function setWebsite(TrackingWebsite $website = null)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return TrackingWebsite
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return int
     */
    public function getVisitCount()
    {
        return $this->visitCount;
    }

    /**
     * @param int $visitCount
     * @return TrackingEventSummary
     */
    public function setVisitCount($visitCount)
    {
        $this->visitCount = $visitCount;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getName();
    }
}
