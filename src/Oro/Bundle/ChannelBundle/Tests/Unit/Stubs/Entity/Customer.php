<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;

#[ORM\Entity]
class Customer implements ChannelAwareInterface
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Channel::class)]
    #[ORM\JoinColumn(name: 'data_channel_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Channel $dataChannel = null;

    #[ORM\ManyToOne(targetEntity: Account::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Account $account = null;

    /**
     * @var double
     */
    #[ORM\Column(name: 'lifetime', type: 'money', nullable: true)]
    protected $lifetime = 0;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

    #[\Override]
    public function setDataChannel(?Channel $channel = null)
    {
        $this->dataChannel = $channel;
    }

    #[\Override]
    public function getDataChannel()
    {
        return $this->dataChannel;
    }

    /**
     * @param float $lifetime
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
    }

    /**
     * @return float
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }
}
