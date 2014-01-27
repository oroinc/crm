<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

abstract class AbstractLoadeableSoapIterator implements \Iterator, \Countable
{
    /** @var SoapTransport */
    protected $transport;

    /** @var array loaded data */
    protected $data;

    /** @var bool */
    protected $loaded = false;

    /**
     * @param SoapTransport $transport
     */
    public function __construct(SoapTransport $transport)
    {
        $this->transport = $transport;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->load();

        reset($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return key($this->data) !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        $this->load();

        return count($this->data);
    }

    /**
     * Check whenever remote data is loaded, and call real load if not
     */
    protected function load()
    {
        if (false === $this->loaded) {
            $this->data   = $this->getData();
            $this->loaded = true;
        }
    }

    /**
     * Do modifications with response for collection requests
     * Fix issues related to specific results in WSI mode
     *
     * @param mixed $response
     *
     * @return array
     */
    protected function processCollectionResponse($response)
    {
        if (!is_array($response)) {
            if ($response && is_object($response)) {
                // response is object, but might be empty in case when no data in WSI mode
                $data = get_object_vars($response);
                if (empty($data)) {
                    $response = [];
                } else {
                    // single result in WSI mode
                    $response = [$response];
                }
            } else {
                // for empty results in Soap V2
                $response = [];
            }
        }

        return $response;
    }

    /**
     * Do load from remote instance
     *
     * @return array
     */
    abstract protected function getData();
}
