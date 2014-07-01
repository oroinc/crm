<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Oro\Component\Config\Resolver\ResolverInterface;

class SettingsProvider
{
    /** @var array */
    protected $settings = [];

    /** @var ResolverInterface */
    protected $resolver;

    /**
     * @param array             $settings
     * @param ResolverInterface $resolver
     */
    public function __construct(array $settings, ResolverInterface $resolver)
    {
        $this->settings = $settings;
        $this->resolver = $resolver;
    }
}
