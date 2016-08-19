<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\ImportExport\Strategy;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\ImportExport\Strategy\B2bConfigurableAddOrReplaceStrategy;
use OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy\AbstractStrategyTest;

class B2bConfigurableAddOrReplaceStrategyTest extends AbstractStrategyTest
{
    /**
     * {@inheritdoc}
     */
    protected function getStrategy()
    {
        $strategy = new B2bConfigurableAddOrReplaceStrategy(
            $this->eventDispatcher,
            $this->strategyHelper,
            $this->fieldHelper,
            $this->databaseHelper,
            $this->chainEntityClassNameProvider,
            $this->translator,
            $this->newEntitiesHelper,
            $this->doctrineHelper
        );

        $strategy->setOwnerHelper($this->defaultOwnerHelper);
        $strategy->setLogger($this->logger);
        $strategy->setChannelHelper($this->channelHelper);
        $strategy->setAddressHelper($this->addressHelper);

        return $strategy;
    }

    /**
     * @param array $properties
     *
     * @return B2bCustomer
     */
    protected function getEntity(array $properties = [])
    {
        $b2bCustomer = new B2bCustomer();

        $channel = new Channel();
        $b2bCustomer->setDataChannel($channel);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($properties as $property => $value) {
            $propertyAccessor->setValue($b2bCustomer, $property, $value);
        }

        return $b2bCustomer;
    }
}
