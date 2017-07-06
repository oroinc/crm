<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator;

abstract class AbstractLoadeableIterator implements \Iterator, \Countable
{
    /** @var array loaded data */
    protected $data;

    /** @var bool */
    protected $loaded = false;

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
     * Do load from remote instance
     *
     * @return array
     */
    abstract protected function getData();
}
