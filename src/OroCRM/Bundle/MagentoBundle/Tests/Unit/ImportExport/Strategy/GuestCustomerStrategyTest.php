<?php
/**
 * Created by PhpStorm.
 * User: vladyslav
 * Date: 5/25/16
 * Time: 6:46 PM
 */

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;


use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\GuestCustomerStrategy;

class GuestCustomerStrategyTest extends AbstractStrategyTest
{
    /**
     * @return GuestCustomerStrategy
     */
    protected function getStrategy()
    {
        $strategy = new GuestCustomerStrategy(
            $this->eventDispatcher,
            $this->strategyHelper,
            $this->fieldHelper,
            $this->databaseHelper,
            $this->chainEntityClassNameProvider,
            $this->translator
        );

        $strategy->setOwnerHelper($this->defaultOwnerHelper);
        $strategy->setChannelHelper($this->channelHelper);

        $this->databaseHelper->expects($this->any())->method('getEntityReference')
            ->will($this->returnArgument(0));

        $strategy->setImportExportContext(
            $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')
            ->disableOriginalConstructor()
            ->getMock()
        );
        $strategy->setEntityName('OroCRM\Bundle\MagentoBundle\Entity\Customer');

        return $strategy;
    }

    public function testProcessEmptyEntity()
    {
        $customer = new Customer();
        $customer->setChannel(new Channel());

        $this->assertNotEmpty($this->getStrategy()->process($customer));
    }

}
