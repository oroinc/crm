<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AccountBundle\Entity\Account;

#[ORM\Entity]
class LifetimeValueHistory
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'status', type: Types::BOOLEAN, nullable: false)]
    protected ?bool $status = null;

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
    protected $amount;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $createdAt = null;
}
