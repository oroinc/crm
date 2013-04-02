<?php

namespace Oro\Bundle\NavigationBundle\Event;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\NavigationBundle\Entity\Builder\ItemFactory;
use Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem;

class ResponseHistoryListener
{
    protected $_navItemFactory = null,
              $_user = null,
              $_em = null;

    public function __construct(ItemFactory $navigationItemFactory, $securityContext, EntityManager $entityManager)
    {
        $this->_navItemFactory = $navigationItemFactory;
        $this->_user = $securityContext->getToken() ? $securityContext->getToken()->getUser() : null;
        $this->_user = $this->_user == 'anon.' ? null : $this->_user;
        $this->_em = $entityManager;
    }

    public function onResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $route = $request->get('_route');

        // do not process requests other than in html format with 200 OK status using GET method and not _internal and _wdt
        if ($response->getStatusCode() != 200 || $request->getRequestFormat() != 'html' || $request->getMethod() != 'GET' ||  $route[0] == '_' || is_null($this->_user)) {
            return false;
        }

        $title = 'Default Title';
        if (preg_match('#<title>([^<]+)</title>#msi', $response->getContent(), $match)) {
            $title = $match[1];
        }

        $postArray = array(
            'title'    => $title,
            'url'      => $request->getRequestUri(),
            'user'     => $this->_user,
        );

        $historyItem = $this->_em->getRepository('Oro\Bundle\NavigationBundle\Entity\NavigationHistoryItem')->findOneBy($postArray);
        if ($historyItem) {
            $historyItem->setPosition(0);
            $historyItem->setCreatedAt( new \DateTime() );
        }
        else {
            $postArray['position'] = 0;
            /** @var $historyItem \Oro\Bundle\NavigationBundle\Entity\NavigationItemInterface */
            $historyItem = $this->_navItemFactory->createItem(NavigationHistoryItem::NAVIGATION_HISTORY_ITEM_TYPE, $postArray);
        }

        $this->_em->persist($historyItem);
        $this->_em->flush();
    }
}
