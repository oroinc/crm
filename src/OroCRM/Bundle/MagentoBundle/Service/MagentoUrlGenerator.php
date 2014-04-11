<?php

namespace OroCRM\Bundle\MagentoBundle\Service;

use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Exception\ExtensionRequiredException;

class MagentoUrlGenerator
{
    const GATEWAY_ROUTE   = 'oro_gateway/do';
    const NEW_ORDER_ROUTE = 'oro_sales/newOrder';
    const ERROR_MESSAGE = 'orocrm.magento.controller.transport_not_configure';

    /**
     * @var Channel
     */
    private $channel;

    /**
     * @var string
     */
    private $error;

    /**
     * @var string
     */
    private $sourceUrl;

    /**
     * @var string
     */
    private $flowName;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var string
     */
    private $origin;

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
     * @return bool
     */
    public function isChannel()
    {
        return !empty($this->channel);
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
     * @throws ExtensionRequiredException
     */
    public function getAdminUrl()
    {
        $url = false;
        if ($this->isChannel()) {
            $transport = $this->getChannel()->getTransport();
            if (!empty($transport)) {
                $url = $transport->getAdminUrl();
            }
        }
        if (empty($url)) {
            throw new ExtensionRequiredException();
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
                '%s/%s?' .
                $this->getOrigin() .
                '=%d&route=%s&workflow=%s&success_url=%s&error_url=%s',
                rtrim($this->getAdminUrl(), '/'),
                self::GATEWAY_ROUTE,
                $id,
                self::NEW_ORDER_ROUTE,
                $this->getFlowName(),
                urlencode($this->generateUrl($successRoute, [], UrlGeneratorInterface::ABSOLUTE_URL)),
                urlencode($this->generateUrl($errorRoute, [], UrlGeneratorInterface::ABSOLUTE_URL))
            );
        } catch (ExtensionRequiredException $e) {
            $this->setError($e->getMessage());
        } catch (\LogicException $e) {
            $this->setError(self::ERROR_MESSAGE);
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
    private function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $url = $this->getRouter()->generate($route, $parameters, $referenceType);
        if (empty($url)) {
            throw new RouteNotFoundException('orocrm.magento.exception.route_not_found');
        }
        return $url;
    }

}
