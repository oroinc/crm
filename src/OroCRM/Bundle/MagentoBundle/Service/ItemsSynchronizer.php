<?php
namespace OroCRM\Bundle\MagentoBundle\Service;

class ItemsSynchronizer
{

    private $connector;

    private $em;

    private $orderConnector;

    private $processor;

    private $item;

    private $error;

    private $redirectUrl;

    /**
     * @param $em doctrine.orm.entity_manager
     * @param $orderConnector orocrm_magento.mage.order_connector
     * @param $processor oro_integration.sync.processor
     */
    public function __construct($em, $orderConnector, $processor)
    {
        $this->connector = false;

        $this
            ->setEm($em)
            ->setOrderConnector($orderConnector)
            ->setProcessor($processor)
        ;
    }

    /**
     * @param mixed $connector
     *
     * @return $this
     */
    public function setConnector($connector)
    {
        $this->connector = $connector;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * @param mixed $em
     *
     * @return $this
     */
    public function setEm($em)
    {
        $this->em = $em;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * @param mixed $orderConnector
     *
     * @return $this
     */
    public function setOrderConnector($orderConnector)
    {
        $this->orderConnector = $orderConnector;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderConnector()
    {
        return $this->orderConnector;
    }

    /**
     * @param mixed $processor
     *
     * @return $this
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @param mixed $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param mixed $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return !empty($this->error);
    }

    /**
     * @param mixed $redirectUrl
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }



    public function sync($itemName, $errorRedirectRoute)
    {
        try {
            $this->getProcessor()->process(
                $this->getItem()->getChannel(),
                #todo: add exception
                $this->getConnector()->getType(),
                ['filters' => ['entity_id' => $this->getItem()->getOriginId()]]
            );

            $this->getProcessor()->process(
                $this->getItem()->getChannel(),
                $this->getOrderConnector()->getType(),
                ['filters' => ['quote_id' => $this->getItem()->getOriginId()]]
            );

            $order = $this
                        ->getEm()
                        ->getRepository('OroCRMMagentoBundle:Order')
                        ->getLastPlacedOrderBy($this->getItem(), $itemName);

            if (null === $order) {
                throw new \LogicException('Unable to load order.');
            }
            #todo: add generateUrl
            #$this->generateUrl('orocrm_magento_order_view', ['id' => $order->getId()]);
            $this->setError('orocrm.magento.controller.synchronization_success');

        } catch (\Exception $e) {
            #todo: add field
            #$this->getItem()->setStatusMessage('orocrm.magento.controller.synchronization_failed_status');

            var_dump($e->getMessage());

            // in import process we have EntityManager#clear()
            $item = $this->getEm()->merge($this->getItem());
            $this->getEm()->flush();
            #todo: add generateUrl
            #$this->generateUrl($errorRedirectRoute, ['id' =>  $item->getId()]);
            $this->setError('orocrm.magento.controller.synchronization_error');
            unset($item);
        }

        return $this;
    }
}
