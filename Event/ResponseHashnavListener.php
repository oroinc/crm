<?php
namespace Oro\Bundle\NavigationBundle\Event;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;

class ResponseHashnavListener
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $security;

    public function __construct(SecurityContextInterface $security)
    {
        $this->security = $security;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (($request->get('x-oro-hash-navigation') || $request->headers->get('x-oro-hash-navigation'))
            && $response->getStatusCode() == 302) {

            $documentRedirect = '';
            if (!$this->security->getToken()) {
                $documentRedirect = 'data-redirect=true';
            }

            $response = '<div id="redirect" ' . $documentRedirect . '>' . $response->headers->get('location') . '</div>';
            $event->setResponse(new Response($response));
        }
    }
}