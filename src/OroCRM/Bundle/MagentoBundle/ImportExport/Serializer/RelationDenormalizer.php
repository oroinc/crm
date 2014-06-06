<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Serializer;

use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class RelationDenormalizer implements DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        /** @var Store|Website|CustomerGroup $result */
        $result = new $class();
        if (!is_array($data)) {
            return $result;
        }

        foreach (['id', 'code', 'name', 'originId'] as $name) {
            $method = 'set' . ucfirst($name);
            if (method_exists($result, $method) && isset($data[$name])) {
                $result->$method($data[$name]);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return in_array(
            $type,
            [
                MagentoConnectorInterface::STORE_TYPE,
                MagentoConnectorInterface::WEBSITE_TYPE,
                MagentoConnectorInterface::CUSTOMER_GROUPS_TYPE
            ]
        );
    }
}
