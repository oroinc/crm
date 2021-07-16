<?php

namespace Oro\Bundle\ChannelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;

/**
 * Represents a record in lifetime history.
 *
 * @ORM\Entity(
 *     repositoryClass="Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository"
 * )
 * @ORM\Table(name="orocrm_channel_lifetime_hist", indexes={
 *      @ORM\Index(name="orocrm_chl_ltv_hist_idx", columns={"account_id", "data_channel_id", "status"}),
 *      @ORM\Index(name="orocrm_chl_ltv_hist_status_idx", columns={"status"})
 * })
 * @ORM\HasLifecycleCallbacks
 */
class LifetimeValueHistory implements ChannelAwareInterface
{
    const STATUS_NEW = 1;
    const STATUS_OLD = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    protected $status = self::STATUS_NEW;

    /**
     * @var Channel
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ChannelBundle\Entity\Channel")
     * @ORM\JoinColumn(name="data_channel_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $dataChannel;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AccountBundle\Entity\Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $account;

    /**
     * @var double
     *
     * @ORM\Column(name="amount", type="money", nullable=false)
     */
    protected $amount = 0;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param Channel $dataChannel
     *
     * remove null after BAP-5248
     */
    public function setDataChannel(Channel $dataChannel = null)
    {
        $this->dataChannel = $dataChannel;
    }

    /**
     * @return Channel
     */
    public function getDataChannel()
    {
        return $this->dataChannel;
    }

    public function setAccount(Account $account)
    {
        $this->account = $account;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param double $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return double
     */
    public function getAmount()
    {
        return $this->amount;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}
