<?php

namespace Oro\Bundle\MagentoBundle\Tests\Behat\Mock\Handler;

class TransportHandler
{
    /**
     * @return array
     */
    public function getCheckResponse()
    {
        return  [
            'success' => true,
            'websites' => [
                [
                    'id' => 1,
                    'label' => 'Website 1',
                ],
            ],
            'isExtensionInstalled' => true,
            'magentoVersion' => '1.2.3',
            'extensionVersion' => '3.2.1',
            'requiredExtensionVersion' => '3.2.1',
            'isOrderNoteSupportExtensionVersion' => true,
            'isSupportedVersion' => true,
            'connectors' => [],
            'adminUrl' => 'adminUrl'
        ];
    }
}
