<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Transport\RestTransportSettingsInterface;
use Oro\Bundle\MagentoBundle\Entity\MagentoRestTransport;

/**
 * Class RestTransportAdapter converts MagentoRestTransport entity to interface suitable for REST client factory
 */
class RestTransportAdapter implements RestTransportSettingsInterface
{
    /** @var MagentoRestTransport */
    protected $transportEntity;

    /** @var array */
    protected $additionalParams;

    /** @var array */
    protected $defaultParams = [
        'headers' => [
            'Accept' => 'application/json'
        ]
    ];

    /**
     * RestTransportAdapter constructor.
     *
     * @param MagentoRestTransport $transportEntity
     * @param array $additionalParams
     */
    public function __construct(MagentoRestTransport $transportEntity, array $additionalParams = [])
    {
        $this->transportEntity = $transportEntity;
        $this->additionalParams = $additionalParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return rtrim($this->transportEntity->getApiUrl(), '/') . '/' . ltrim(RestTransport::API_URL_PREFIX, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return  array_merge($this->defaultParams, $this->additionalParams);
    }
}
