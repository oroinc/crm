<?php

namespace Oro\Bundle\ChannelBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;

class LifetimeValueExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'oro_channel_lifetime_value';

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return AmountProvider
     */
    protected function getAmountProvider()
    {
        return $this->container->get('oro_channel.provider.lifetime.amount_provider');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('oro_channel_account_lifetime', [$this, 'getLifetimeValue'])
        ];
    }

    /**
     * @param Account $account
     * @param Channel $channel
     *
     * @return float
     */
    public function getLifetimeValue(Account $account, Channel $channel = null)
    {
        return $this->getAmountProvider()->getAccountLifeTimeValue($account, $channel);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }
}
