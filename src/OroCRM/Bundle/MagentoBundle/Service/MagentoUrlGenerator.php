<?php
namespace OroCRM\Bundle\MagentoBundle\Service;

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

    private $route;

    /**
     *
     */
    public function __construct(Symfony\Component\Routing\Route $Route)
    {
        $this->channel   = false;
        $this->error     = false;
        $this->sourceUrl = false;
        $this->flowName  = false;
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
     * @return mixed
     */
    public function getAdminUrl()
    {
        return $this->getChannel()->getTransport()->getAdminUrl();

        if (false === $url) {
            throw new ExtensionRequiredException();
        }

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
     * @param string $successUrl
     * @param string $errorUrl
     *
     * @return $this
     */
    public function setSourceUrl($id, $successUrl, $errorUrl)
    {
        try {
            $this->sourceUrl = sprintf(
                '%s/%s?quote=%d&route=%s&workflow=%s&success_url=%s&error_url=%s',
                rtrim($this->getAdminUrl(), '/'),
                self::GATEWAY_ROUTE,
                $id,
                self::NEW_ORDER_ROUTE,
                $this->getFlowName(),
                urlencode($successUrl),
                urlencode($errorUrl)
            );

        } catch (ExtensionRequiredException $e) {
            $this->error = $e->getMessage();
        } catch (\LogicException $e) {
            $this->error = self::ERROR_MESSAGE;
        }

        return $this;
    }
}
