<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Processor\OrderNotes;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderNote;

class ChainProcessor
{
    /**
     * @var array|ProcessorInterface[]
     */
    private $processors;

    /**
     * @param ProcessorInterface $processor
     */
    public function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * @param Order $order
     */
    public function processNotes(Order $order)
    {
        if (empty($this->processors)) {
            return;
        }

        /**
         * @var ArrayCollection|OrderNote[]
         */
        $orderNotes = $order->getOrderNotes();
        foreach ($orderNotes as $orderNote) {
            foreach ($this->processors as $processor) {
                $context = Context::createContext($order, $orderNote);
                $processor->process($context);
                if ($context->isItemSkipped()) {
                    break;
                }
            }
        }
    }
}
