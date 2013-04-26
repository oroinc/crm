<?php
namespace Oro\Bundle\NavigationBundle\Event;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;

class ResponseHashnavListener
{
    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        if ($request->headers->get('x-oro-hash-navigation') && $response->getStatusCode() == 302)
        {
            $response = '<div id="redirect">' . $response->headers->get('location') . '</div>';
            $event->setResponse(new Response($response));
        }
    }
}