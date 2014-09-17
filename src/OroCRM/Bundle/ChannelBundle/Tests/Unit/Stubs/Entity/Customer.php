<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class Customer
{
    /** @var int */
    protected $id;

    /** @var Account */
    protected $account;

    /** @var Channel */
    protected $dataChannel;

    /** @var float */
    protected $lifetime;

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
     * @param Channel $dataChannel
     */
    public function setDataChannel($dataChannel)
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
