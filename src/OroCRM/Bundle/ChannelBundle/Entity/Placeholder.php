<?php
namespace OroCRM\Bundle\ChannelBundle\Entity;

use OroCRM\Bundle\ChannelBundle\Model\CustomerIdentityInterface;
use OroCRM\Bundle\ChannelBundle\Model\ChannelEntityTrait;

class Placeholder implements CustomerIdentityInterface
{
    use ChannelEntityTrait;

    /**
     * @return null
     */
    public function getAccount()
    {
        return null;
    }
}
