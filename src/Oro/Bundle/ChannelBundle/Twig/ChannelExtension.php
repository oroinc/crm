<?php

namespace Oro\Bundle\ChannelBundle\Twig;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;
use Oro\Bundle\ChannelBundle\Provider\MetadataProviderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides Twig functions to retrieve channel metadata associated with an entity:
 *   - oro_channel_entities_metadata
 *   - oro_channel_type_metadata
 *
 * Provides a Twig function for the per-channel account lifetime value:
 *   - oro_channel_account_lifetime
 */
class ChannelExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('oro_channel_entities_metadata', [$this, 'getEntitiesMetadata']),
            new TwigFunction('oro_channel_type_metadata', [$this, 'getChannelTypeMetadata']),
            new TwigFunction('oro_channel_account_lifetime', [$this, 'getLifetimeValue'])
        ];
    }

    /**
     * @return array
     */
    public function getEntitiesMetadata()
    {
        return $this->getMetadataProvider()->getEntitiesMetadata();
    }

    /**
     * @return array
     */
    public function getChannelTypeMetadata()
    {
        return array_flip($this->getMetadataProvider()->getChannelTypeMetadata());
    }

    /**
     * @param Account      $account
     * @param Channel|null $channel
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
    public static function getSubscribedServices()
    {
        return [
            'oro_channel.provider.metadata_provider' => MetadataProviderInterface::class,
            'oro_channel.provider.lifetime.amount_provider' => AmountProvider::class,
        ];
    }

    private function getMetadataProvider(): MetadataProviderInterface
    {
        return $this->container->get('oro_channel.provider.metadata_provider');
    }

    private function getAmountProvider(): AmountProvider
    {
        return $this->container->get('oro_channel.provider.lifetime.amount_provider');
    }
}
