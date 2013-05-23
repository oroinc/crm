<?php
namespace Oro\Bundle\NavigationBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router as Router;

class ResponsePagestateListener
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $security;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    protected $templating;

    public function __construct(Router $router, EngineInterface $templating, SecurityContextInterface $security)
    {

        $this->router     = $router;
        $this->security   = $security;
        $this->templating = $templating;
    }

    /**
     * Process onResponse event
     *
     * @param  FilterResponseEvent $event
     * @return bool|void
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        if (!is_object($this->security->getToken())
            && HttpKernel::MASTER_REQUEST == $event->getRequestType()
            && $request->getMethod() == 'PUT'
            && $request->getRequestFormat() == 'json'
            && $response->getStatusCode() == 401
        ) {
            return $event->setResponse(
                $this->templating->renderResponse(
                    'OroNavigationBundle:Pagestate:redirect.html.twig',
                    array(
                        'location'      => $this->router->generate('oro_user_security_login'),
                    )
                )
            );
        }

        return;
    }
}
