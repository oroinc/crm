<?php

namespace Oro\Bundle\ContactBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\ContactBundle\Formatter\SocialUrlFormatter;

class ContactExtension extends \Twig_Extension
{
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
     * @return SocialUrlFormatter
     */
    protected function getSocialUrlFormatter()
    {
        return $this->container->get('oro_contact.social_url_formatter');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_contact_social_url';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('oro_social_url', [$this, 'getSocialUrl']),
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
}
