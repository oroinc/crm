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
    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return SocialUrlFormatter
     */
    protected function getSocialUrlFormatter()
    {
        return $this->container->get('oro_contact.social_url_formatter');
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return [
            'oro_contact.social_url_formatter' => SocialUrlFormatter::class,
        ];
    }
}
