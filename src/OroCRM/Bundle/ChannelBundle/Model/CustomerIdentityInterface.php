<?php

namespace OroCRM\Bundle\ChannelBundle\Model;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

interface CustomerIdentityInterface
{
    /**
     * @return Account
     */
    public function getAccount();

    /**
     * @return Channel
     */
    public function getDataChannel();
}
