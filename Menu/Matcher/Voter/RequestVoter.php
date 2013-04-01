<?php

namespace Oro\Bundle\NavigationBundle\Menu\Matcher\Voter;

use Knp\Menu\Matcher\Voter\UriVoter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RequestVoter extends UriVoter
{
    /**
     * Constructor
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $uri = $container->get('request')->getRequestUri();

        parent::__construct($uri);
    }
}
