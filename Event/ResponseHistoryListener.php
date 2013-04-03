<?php

namespace Oro\Bundle\NavigationBundle\Event;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory;
use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;

class ResponseHistoryListener
{
    /**
     * @var null|\Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory
     */
    protected $navItemFactory = null;

    /**
     * @var User|String
     */
    protected $user  = null;

    /**
     * @var \Doctrine\ORM\EntityManager|null
     */
    protected $em = null;

    public function __construct(
        ItemFactory $navigationItemFactory,
        SecurityContextInterface $securityContext,
        EntityManager $entityManager
    ) {
        $this->navItemFactory = $navigationItemFactory;
        $this->user = !$securityContext->getToken() ||  is_string($securityContext->getToken()->getUser())
                      ? null : $securityContext->getToken()->getUser();

        $this->em = $entityManager;
    }

    public function onResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // do not process requests other than in html format
        // with 200 OK status using GET method and not _internal and _wdt
        if (!$this->matchRequest($response, $request)) {
            return false;
        }

        $title = 'Default Title';
        if (preg_match('#<title>([^<]+)</title>#msi', $response->getContent(), $match)) {
            $title = $match[1];
        }

        $postArray = array(
            'url'      => $request->getRequestUri(),
            'user'     => $this->user,
        );

        $historyItem = $this->em->getRepository('Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem')
                                ->findOneBy($postArray);
        if (!$historyItem) {
            /** @var $historyItem \Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface */
            $historyItem = $this->navItemFactory->createItem(
                NavigationHistoryItem::NAVIGATION_HISTORY_ITEM_TYPE,
                $postArray
            );
        }

        $historyItem->setTitle($title);

        // force update
        $historyItem->doPreUpdate();

        $this->em->persist($historyItem);
        $this->em->flush();
    }

    /**
     * Is request valid for adding to history
     *
     * @param $response
     * @param $request
     * @return bool
     */
    private function matchRequest($response, $request)
    {
        $route = $request->get('_route');

        return !($response->getStatusCode() != 200
            || $request->getRequestFormat() != 'html'
            || $request->getMethod() != 'GET'
            || $route[0] == '_'
            || is_null($this->user));
    }
}
