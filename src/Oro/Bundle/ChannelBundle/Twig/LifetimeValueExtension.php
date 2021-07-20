<?php

namespace Oro\Bundle\ChannelBundle\Twig;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a Twig function for the per-channel account lifetime value:
 *   - oro_channel_account_lifetime
 */
class LifetimeValueExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    const EXTENSION_NAME = 'oro_channel_lifetime_value';

    /** @var ContainerInterface */
    protected $container;

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
            new TwigFunction('oro_channel_account_lifetime', [$this, 'getLifetimeValue'])
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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_channel.provider.lifetime.amount_provider' => AmountProvider::class,
        ];
    }
}
