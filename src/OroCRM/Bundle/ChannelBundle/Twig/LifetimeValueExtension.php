<?php

namespace OroCRM\Bundle\ChannelBundle\Twig;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;

class LifetimeValueExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'orocrm_channel_lifetime_value';

    /** @var AmountProvider */
    protected $amountProvider;

    /**
     * @param AmountProvider $amountProvider
     */
    public function __construct(AmountProvider $amountProvider)
    {
        $this->amountProvider = $amountProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $lifetimeValue = new \Twig_SimpleFunction('orocrm_channel_account_lifetime', [$this, 'getLifetimeValue']);

        return [$lifetimeValue->getName() => $lifetimeValue];
    }

    /**
     * @param Account $account
     * @param Channel $channel
     *
     * @return float
     */
    public function getLifetimeValue(Account $account, Channel $channel = null)
    {
        return $this->amountProvider->getAccountLifeTimeValue($account, $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }
}
