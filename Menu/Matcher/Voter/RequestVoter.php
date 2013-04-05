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
        $uri = null;

        // TODO BAP-430 do correct processing of scope to avoid problems with CLI environment
        if ($container->isScopeActive('request')) {
            $container->get('request')->getRequestUri();
        }

        parent::__construct($uri);
    }
}
