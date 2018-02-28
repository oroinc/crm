<?php

namespace Oro\Bundle\MagentoBundle\Service;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Exception\AdminUrlRequiredException;
use Oro\Bundle\MagentoBundle\Exception\Exception as MagentoBundleException;
use Oro\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class MagentoUrlGenerator
{
    const GATEWAY_ROUTE = 'oro_gateway/do';
    const NEW_ORDER_ROUTE = 'oro_sales/newOrder';
    const CHECKOUT_ROUTE = 'oro_sales/checkout';
    const EXTENSION_REQUIRED_ERROR_MESSAGE = 'oro.magento.controller.extension_required';
    const DEFAULT_ERROR_MESSAGE = 'oro.magento.controller.transport_not_configure';

    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var string
     */
    protected $sourceUrl;

    /**
     * @var string
     */
    protected $flowName;

    /**
     * @var string
     */
    protected $magentoRoute;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var string
     */
    protected $origin;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->channel   = null;
        $this->error     = '';
        $this->sourceUrl = '';
        $this->flowName  = '';
        $this->origin    = '';
        $this->router    = $router;
    }

    /**
     * @param Channel $channel
     *
     * @return $this
     */
    public function setChannel(Channel $channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     *
     * @return $this
     */
    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return !empty($this->error);
    }

    /**
     * @param string $flowName
     *
     * @return $this
     */
    public function setFlowName($flowName)
    {
        $this->flowName = $flowName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFlowName()
    {
        return $this->flowName;
    }

    /**
     * @param string $magentoRoute
     * @return $this
     */
    public function setMagentoRoute($magentoRoute)
    {
        $this->magentoRoute = $magentoRoute;
        return $this;
    }

    /**
     * @return string
     */
    public function getMagentoRoute()
    {
        return $this->magentoRoute ? : self::NEW_ORDER_ROUTE;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param string $origin
     *
     * @return $this
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @return string
     * @throws AdminUrlRequiredException
     */
    public function getAdminUrl()
    {
        $url = false;

        if ($this->getChannel() && $this->getChannel()->getTransport()) {
            $transport = $this->getChannel()->getTransport();
            if ($transport instanceof MagentoTransport) {
                $url = $transport->getAdminUrl();
            }
        }

        if (!$url) {
            throw new AdminUrlRequiredException();
        }

        return $url;
    }

    /**
     * @return string
     */
    public function getSourceUrl()
    {
        return $this->sourceUrl;
    }

    /**
     * @param int $id
     * @param string $successRoute
     * @param string $errorRoute
     *
     * @return $this
     */
    public function generate($id, $successRoute, $errorRoute)
    {
        try {
            $this->sourceUrl = sprintf(
                '%s/%s?%s=%d&route=%s&workflow=%s&success_url=%s&error_url=%s',
                rtrim($this->getAdminUrl(), '/'),
                self::GATEWAY_ROUTE,
                $this->getOrigin(),
                $id,
                $this->getMagentoRoute(),
                $this->getFlowName(),
                urlencode($this->generateUrl($successRoute, [], UrlGeneratorInterface::ABSOLUTE_URL)),
                urlencode($this->generateUrl($errorRoute, [], UrlGeneratorInterface::ABSOLUTE_URL))
            );
        } catch (ExtensionRequiredException $e) {
            $this->setError(self::EXTENSION_REQUIRED_ERROR_MESSAGE);
        } catch (MagentoBundleException $e) {
            $this->setError(self::DEFAULT_ERROR_MESSAGE);
        }
        return $this;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string         $route         The name of the route
     * @param array          $parameters    An array of parameters
     * @param Boolean|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @throws RouteNotFoundException
     *
     * @see UrlGeneratorInterface
     */
    protected function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $url = $this->getRouter()->generate($route, $parameters, $referenceType);
        if (empty($url)) {
            throw new RouteNotFoundException('Route cannot be generated, route not found.');
        }
        return $url;
    }
}
