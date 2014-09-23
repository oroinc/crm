<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity;

use Doctrine\ORM\Mapping as ORM;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Model\ChannelEntityTrait;
use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;

/**
 * @ORM\Entity
 * @ORM\MappedSuperclass
 */
class Customer implements ChannelAwareInterface
{
    use ChannelEntityTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account", cascade="PERSIST")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $account;

    /**
     * @var double
     *
     * @ORM\Column(name="lifetime", type="money", nullable=true)
     */
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

    /**
     * @param Account $account
     */
    public function setAccount($account)
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
