<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Processor\OrderNotes;

use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderNote;

class Context
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var OrderNote
     */
    private $orderNote;

    /**
     * @var bool
     */
    private $skip = false;

    /**
     * @param Order     $order
     * @param OrderNote $orderNote
     *
     * @return Context
     */
    public static function createContext(Order $order, OrderNote $orderNote)
    {
        return new self($order, $orderNote);
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return OrderNote
     */
    public function getOrderNote()
    {
        return $this->orderNote;
    }

    /**
     * @return boolean
     */
    public function isItemSkipped()
    {
        return true === $this->skip;
    }

    /**
     * @return $this
     */
    public function markItemSkipped()
    {
        $this->skip = true;

        return $this;
    }

    /**
     * @param Order $order
     * @param OrderNote $orderNote
     *
     * A private constructor to prevent create an instance of this class explicitly
     */
    private function __construct(Order $order, OrderNote $orderNote)
    {
        $this->order = $order;
        $this->orderNote = $orderNote;
    }
}
