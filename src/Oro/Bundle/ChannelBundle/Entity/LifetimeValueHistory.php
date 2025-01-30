<?php

namespace Oro\Bundle\ChannelBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeHistoryRepository;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;

/**
 * Represents a record in lifetime history.
 */
#[ORM\Entity(repositoryClass: LifetimeHistoryRepository::class)]
#[ORM\Table(name: 'orocrm_channel_lifetime_hist')]
#[ORM\Index(columns: ['account_id', 'data_channel_id', 'status'], name: 'orocrm_chl_ltv_hist_idx')]
#[ORM\Index(columns: ['status'], name: 'orocrm_chl_ltv_hist_status_idx')]
#[ORM\HasLifecycleCallbacks]
class LifetimeValueHistory implements ChannelAwareInterface
{
    const STATUS_NEW = true;
    const STATUS_OLD = false;

    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'status', type: Types::BOOLEAN, nullable: false)]
    protected ?bool $status = self::STATUS_NEW;

    #[ORM\ManyToOne(targetEntity: Channel::class)]
    #[ORM\JoinColumn(name: 'data_channel_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Channel $dataChannel = null;

    #[ORM\ManyToOne(targetEntity: Account::class)]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Account $account = null;

    /**
     * @var double
     */
    #[ORM\Column(name: 'amount', type: 'money', nullable: false)]
    protected $amount = 0;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $createdAt = null;

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
        $this->status = (bool) $status;
    }

    /**
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param Channel|null $dataChannel
     *
     * remove null after BAP-5248
     */
    #[\Override]
    public function setDataChannel(?Channel $dataChannel = null)
    {
        $this->dataChannel = $dataChannel;
    }

    /**
     * @return Channel
     */
    #[\Override]
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

    #[ORM\PrePersist]
    public function prePersist()
    {
        if (null === $this->createdAt) {
            $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        }
    }

    /**
     * @return string
     */
    #[\Override]
    public function __toString()
    {
        return (string)$this->getId();
    }
}
