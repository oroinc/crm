<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Provider\Reader\ContextOrderReader;

class CreditMemoWithExistingOrderStrategy extends CreditMemoStrategy
{
    const CONTEXT_CREDIT_MEMO_POST_PROCESS = 'postProcessCreditMemos';

    /**
     * @param CreditMemo $importingCreditMemo
     *
     * {@inheritdoc}
     */
    public function process($importingCreditMemo)
    {
        if (!$this->isProcessingAllowed($importingCreditMemo)) {
            $this->appendDataToContext(self::CONTEXT_CREDIT_MEMO_POST_PROCESS, $this->context->getValue('itemData'));

            return null;
        }

        return parent::process($importingCreditMemo);
    }

    /**
     * @param CreditMemo $creditMemo
     * @return bool
     */
    protected function isProcessingAllowed(CreditMemo $creditMemo)
    {
        $isProcessingAllowed = true;
        $orderOriginId = $creditMemo->getOrder()->getOriginId();
        $order = $this->findExistingEntity($creditMemo->getOrder());
        if (!$order && $orderOriginId) {
            //order should be processed before credit memo
            $this->appendDataToContext(ContextOrderReader::CONTEXT_POST_PROCESS_ORDERS, $orderOriginId);

            $isProcessingAllowed = false;
        }

        return $isProcessingAllowed;
    }
}
