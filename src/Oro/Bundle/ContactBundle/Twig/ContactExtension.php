<?php

namespace Oro\Bundle\ContactBundle\Twig;

use Oro\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a Twig function to format URLs to social networks:
 *   - oro_social_url
 */
class ContactExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    #[\Override]
    public function getFunctions()
    {
        return [
            new TwigFunction('oro_social_url', [$this, 'getSocialUrl']),
        ];
    }

    /**
     * @param string $socialType
     * @param string $username
     *
     * @return string
     */
    public function getSocialUrl($socialType, $username)
    {
        if (!$socialType || !$username) {
            return '#';
        }

        return $this->getSocialUrlFormatter()->getSocialUrl($socialType, $username);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            SocialUrlFormatter::class
        ];
    }

    private function getSocialUrlFormatter(): SocialUrlFormatter
    {
        return $this->container->get(SocialUrlFormatter::class);
    }
}
