<?php

namespace Oro\Bundle\MagentoBundle\Handler;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\TransportCacheClearInterface;
use Oro\Bundle\IntegrationBundle\Utils\MultiAttemptsConfigTrait;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Provider\ConnectorChoicesProvider;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\MagentoBundle\Provider\TransportEntityProvider;
use Oro\Bundle\MagentoBundle\Provider\WebsiteChoicesProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Handler has method getCheckResponse which allows to check configuration of the integration
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransportHandler
{
    const INTEGRATION_TYPE  = 'type';
    const TRANSPORT_TYPE    = 'transport';

    /** @var  TypesRegistry */
    protected $typesRegistry;

    /** @var  TransportEntityProvider */
    protected $transportEntityProvider;

    /** @var  WebsiteChoicesProvider */
    protected $websiteProvider;

    /** @var  ConnectorChoicesProvider */
    protected $connectorProvider;

    /** @var  RequestStack */
    protected $requestStack;

    /**
     * @param TypesRegistry             $typesRegistry
     * @param TransportEntityProvider   $transportEntityProvider
     * @param WebsiteChoicesProvider    $websiteProvider
     * @param ConnectorChoicesProvider  $choicesProvider
     * @param RequestStack              $requestStack
     */
    public function __construct(
        TypesRegistry $typesRegistry,
        TransportEntityProvider $transportEntityProvider,
        WebsiteChoicesProvider $websiteProvider,
        ConnectorChoicesProvider $choicesProvider,
        RequestStack $requestStack
    ) {
        $this->typesRegistry            = $typesRegistry;
        $this->transportEntityProvider  = $transportEntityProvider;
        $this->websiteProvider          = $websiteProvider;
        $this->connectorProvider        = $choicesProvider;
        $this->requestStack             = $requestStack;
    }

    /**
     * @return array
     */
    public function getCheckResponse()
    {
        $transport = $this->getMagentoTransport();
        $transportEntity = $this->getMagentoTransportEntity($transport);

        $this->initMagentoTransport($transport, $transportEntity);

        $integrationTypeName = $this->requestStack->getCurrentRequest()->get(self::INTEGRATION_TYPE, false);
        $isExtensionInstalled = $transport->isExtensionInstalled();
        $isSupportedVersion = $transport->isSupportedExtensionVersion();
        $extensionVersion = $transport->getExtensionVersion();
        $isOrderNoteSupportExtensionVersion = $transport->isSupportedOrderNoteExtensionVersion();
        $allowedTypesChoices = $this
            ->connectorProvider
            ->getAllowedConnectorsChoices($isExtensionInstalled, $isSupportedVersion, $integrationTypeName);

        return  [
            'success' => true,
            'websites' => $this->websiteProvider->formatWebsiteChoices($transport),
            'isExtensionInstalled' => $isExtensionInstalled,
            'magentoVersion' => $transport->getMagentoVersion(),
            'extensionVersion' => $extensionVersion,
            'requiredExtensionVersion' => $transport->getRequiredExtensionVersion(),
            'isOrderNoteSupportExtensionVersion' => $isOrderNoteSupportExtensionVersion,
            'isSupportedVersion' => $isSupportedVersion,
            'connectors' => $allowedTypesChoices,
            'adminUrl' => $transport->getAdminUrl()
        ];
    }

    /**
     * @param MagentoTransportInterface $transport
     * @param MagentoTransport          $transportEntity
     */
    protected function initMagentoTransport(
        MagentoTransportInterface $transport,
        MagentoTransport $transportEntity
    ) {
        if ($transport instanceof TransportCacheClearInterface) {
            $transport->cacheClear($transportEntity->getApiUrl());
        }

        $transport->initWithExtraOptions($transportEntity, MultiAttemptsConfigTrait::getMultiAttemptsDisabledConfig());
        $transport->resetInitialState();
    }

    /**
     * @return MagentoTransportInterface
     */
    protected function getMagentoTransport()
    {
        $request = $this->requestStack->getCurrentRequest();
        $integrationTypeName = $request->get(self::INTEGRATION_TYPE, false);
        $transportType       = $request->get(self::TRANSPORT_TYPE, false);

        /** @var MagentoTransportInterface $transport */
        $transport = $this->typesRegistry->getTransportType($integrationTypeName, $transportType);

        if (!$transport instanceof MagentoTransportInterface) {
            throw new UnexpectedTypeException($transport, MagentoTransportInterface::class);
        }

        return $transport;
    }

    /**
     * @param MagentoTransportInterface $transport
     *
     * @return MagentoTransport
     */
    private function getMagentoTransportEntity(MagentoTransportInterface $transport)
    {
        return $this->transportEntityProvider->getTransportEntityByRequest(
            $transport,
            $this->requestStack->getCurrentRequest()
        );
    }
}
