<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConnectorChoicesProvider
{
    /** @var  TypesRegistry */
    protected $typesRegistry;

    /** @var  TranslatorInterface */
    protected $translator;

    /**
     * @param TypesRegistry         $typesRegistry
     * @param TranslatorInterface   $translator
     */
    public function __construct(TypesRegistry $typesRegistry, TranslatorInterface $translator)
    {
        $this->typesRegistry    = $typesRegistry;
        $this->translator       = $translator;
    }

    /**
     * @param bool $isExtensionInstalled
     * @param bool $isSupportedVersion
     * @param $integrationType
     *
     * @return \string[]
     */
    public function getAllowedConnectorsChoices($isExtensionInstalled, $isSupportedVersion, $integrationType)
    {
        $allowedTypesChoices = $this->typesRegistry
            ->getAvailableConnectorsTypesChoiceList(
                $integrationType,
                function (ConnectorInterface $connector) use ($isExtensionInstalled, $isSupportedVersion) {
                    if ($connector instanceof ExtensionVersionAwareInterface) {
                        return $isExtensionInstalled && $isSupportedVersion;
                    }

                    if ($connector instanceof ExtensionAwareInterface) {
                        return $isExtensionInstalled;
                    }

                    return true;
                }
            );

        foreach ($allowedTypesChoices as $name => $val) {
            $allowedTypesChoices[$name] = $this->translator->trans($val);
        }

        return $allowedTypesChoices;
    }
}
