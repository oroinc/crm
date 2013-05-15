<?php
namespace Oro\Bundle\NavigationBundle\Event;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class ResponseHashnavListener
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $security;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    protected $templating;

    public function __construct(SecurityContextInterface $security, EngineInterface $templating)
    {
        $this->security = $security;
        $this->templating = $templating;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if (($request->get('x-oro-hash-navigation') || $request->headers->get('x-oro-hash-navigation'))
            && $response->isRedirect()
        ) {
            $event->setResponse(
                $this->templating->renderResponse(
                    'OroNavigationBundle:HashNav:redirect.html.twig',
                    array(
                         'token'    => $this->security->getToken(),
                         'location' => $response->headers->get('location')
                    )
                )
            );
        }
    }
}
