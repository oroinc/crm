<?php
namespace OroCRM\Bundle\MagentoBundle\Service;

use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @var mixed
     */
    private $error;

    /**
     * @var mixed
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
     *
     */
    public function __construct(Router $Router)
    {
        $this->channel   = false;
        $this->error     = false;
        $this->sourceUrl = false;
        $this->flowName  = false;
        $this->setRouter($Router);
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
     * @return bool|Channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
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
     * @param \Symfony\Component\Routing\Router $router
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }

    /**
     * @return \Symfony\Component\Routing\Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string         $route         The name of the route
     * @param mixed          $parameters    An array of parameters
     * @param Boolean|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->getRouter()->generate($route, $parameters, $referenceType);
    }

    /**
     * @return mixed
     * @throws \OroCRM\Bundle\MagentoBundle\Exception\ExtensionRequiredException
     */
    public function getAdminUrl()
    {
        $url = (string)@$this->getChannel()->getTransport()->getAdminUrl();

        if (false === $url || '' === $url || empty($url)) {
            throw new ExtensionRequiredException();
        }
        return $url;
    }

    /**
     * @return mixed
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
    public function setSourceUrl($id, $successRoute, $errorRoute)
    {
        try {
            $this->sourceUrl = sprintf(
                '%s/%s?quote=%d&route=%s&workflow=%s&success_url=%s&error_url=%s',
                rtrim($this->getAdminUrl(), '/'),
                self::GATEWAY_ROUTE,
                $id,
                self::NEW_ORDER_ROUTE,
                $this->getFlowName(),
                urlencode($this->generateUrl($successRoute)),
                urlencode($this->generateUrl($errorRoute))
            );

        } catch (ExtensionRequiredException $e) {
            $this->error = $e->getMessage();
        } catch (\LogicException $e) {
            $this->error = self::ERROR_MESSAGE;
        }

        return $this;
    }
}
