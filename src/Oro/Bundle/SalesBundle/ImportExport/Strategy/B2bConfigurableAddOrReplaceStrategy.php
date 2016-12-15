<?php

namespace Oro\Bundle\SalesBundle\ImportExport\Strategy;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bConfigurableAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        /** @var B2bCustomer $entity */
        $entity = parent::afterProcessEntity($entity);
        $this->guessRegion($entity->getBillingAddress());
        $this->guessRegion($entity->getShippingAddress());

        return $entity;
    }

    /**
     * @param Address $address
     */
    protected function guessRegion($address)
    {
        if ($address
            && $address->getCountry() && $address->getRegionText()
            && !$address->getRegion()
        ) {
            $region = $this->doctrineHelper
                ->getEntityRepository('OroAddressBundle:Region')
                ->findOneBy(
                    [
                        'country' => $address->getCountry(),
                        'name'    => $address->getRegionText()
                    ]
                );
            if ($region) {
                $address->setRegion($region);
                $address->setRegionText(null);
            }
        }
    }
}
