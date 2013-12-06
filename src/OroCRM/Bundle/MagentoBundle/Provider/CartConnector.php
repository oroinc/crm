<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class CartConnector extends AbstractConnector
{
    const ENTITY_NAME         = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Cart';
    const CONNECTOR_LABEL     = 'orocrm.magento.connector.cart.label';
    const JOB_VALIDATE_IMPORT = 'mage_cart_import_validation';
    const JOB_IMPORT          = 'mage_cart_import';

    const ACTION_CART_LIST    = 'salesQuoteList';
    const PAGE_SIZE           = 10;

    /** @var int */
    protected $currentPage = 1;

    /** @var array */
    protected $quoteQueue = [];

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $result = $this->getNextItem();

        if (empty($result)) {
            return null; // no more data
        }

        $result = $this->objectToArray($result);
        $this->currentPage++;

        return (array) $result;
    }

    /**
     * Return next quote data from loaded queue or remote API
     *
     * @return array
     */
    protected function getNextItem()
    {
        $filters = [];

        if (empty($this->quoteQueue)) {
            // TODO: remove / log
            echo sprintf(
                'Looking for entities at %d page ... ',
                $this->currentPage
            );

            $this->quoteQueue = $this->getQuoteList(
                $filters,
                ['page' => $this->currentPage, 'pageSize' => self::PAGE_SIZE]
            );

            // TODO: remove / log
            echo sprintf(
                '%d records',
                count($this->quoteQueue),
                $this->currentPage
            ) . "\n";
        }

        return array_shift($this->quoteQueue);
    }

    /**
     * @param array $filters
     * @param array $limits
     * @return mixed
     */
    public function getQuoteList($filters = [], $limits = [])
    {
        if (empty($limits)) {
            $limits = [
                'page' => 1,
                'pageSize' => 15,
            ];
        }

        return $this->call(self::ACTION_CART_LIST, [$filters, $limits]);
    }
}
