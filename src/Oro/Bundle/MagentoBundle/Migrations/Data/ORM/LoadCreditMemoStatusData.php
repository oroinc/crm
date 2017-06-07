<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;

class LoadCreditMemoStatusData extends AbstractEnumFixture
{
    /**
     * {@inheritdoc}
     */
    protected function getData()
    {
        return [
            CreditMemo::STATUS_OPEN     => 'Pending',
            CreditMemo::STATUS_REFUNDED => 'Refunded',
            CreditMemo::STATUS_CANCELED => 'Canceled',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnumCode()
    {
        return 'creditmemo_status';
    }
}
