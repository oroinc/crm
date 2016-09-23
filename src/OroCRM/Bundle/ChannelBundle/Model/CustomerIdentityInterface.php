<?php

namespace Oro\Bundle\ChannelBundle\Model;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;

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
