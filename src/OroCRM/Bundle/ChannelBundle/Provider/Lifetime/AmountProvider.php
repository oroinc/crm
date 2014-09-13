<?php

namespace OroCRM\Bundle\ChannelBundle\Provider\Lifetime;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class AmountProvider
{
    /** @var LifetimeAmountQueryBuilder */
    protected $amountQueryBuilder;

    /**
     * @param LifetimeAmountQueryBuilder $amountQueryBuilder
     */
    public function __construct(LifetimeAmountQueryBuilder $amountQueryBuilder)
    {
        $this->amountQueryBuilder = $amountQueryBuilder;
    }


    /**
     * @param Account      $account
     * @param Channel|null $channel
     */
    public function getAccountLifeTimeValue(Account $account, Channel $channel = null)
    {
        if (null !== $channel) {

        } else {

        }
    }
}
