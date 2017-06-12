<?php

namespace OroCRM\Bundle\MagentoBundle\Routing;

use Symfony\Component\Routing\Route;

use Oro\Component\Routing\Resolver\RouteCollectionAccessor;
use Oro\Component\Routing\Resolver\RouteOptionsResolverInterface;

/**
 * As FOSRestBundle v1.7.1 generates a plural path for OPTIONS routes,
 * we need to add a single path to avoid BC break.
 * The single path is marked as deprecated.
 *
 * @deprecated since 1.8. Will be removed in 2.0
 */
class OldOptionsRouteOptionsResolver implements RouteOptionsResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(Route $route, RouteCollectionAccessor $routes)
    {
        if ($route->getPath() === '/magento/order/view/{id}') {
            $singleRoute = $routes->cloneRoute($route);
            $singleRoute->setPath('/magentoorder/view/{id}');
            $routes->append('orocrm_magentoorder_view', $singleRoute);
        }

        if (!in_array('GET', $route->getMethods(), true)) {
            return;
        }

        if ($route->getPath() === '/api/rest/{version}/carts/{id}.{_format}') {
            $singleRoute = $routes->cloneRoute($route);
            $singleRoute->setPath('/api/rest/{version}/magentocarts/{id}.{_format}');
            $routes->append('oro_api_get_magentocarts', $singleRoute);
        }

        if ($route->getPath() === '/api/rest/{version}/orders/{id}.{_format}') {
            $singleRoute = $routes->cloneRoute($route);
            $singleRoute->setPath('/api/rest/{version}/magentoorders/{id}.{_format}');
            $routes->append('oro_api_get_magentoorders', $singleRoute);
        }
    }
}
