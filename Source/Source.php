<?php
namespace Oro\Bundle\DataFlowBundle\Source;


use Oro\Bundle\DataFlowBundle\Source\Filter\FilterInterface;

/**
 * Source object
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 * @abstract
 */
abstract class Source implements SourceInterface
{
    /**
     * list of filters used before recovering source
     * @var multitype:FilterInterface
     */
    protected $preFilters = array();

    /**
     * List of filters used after recovering source
     * @var multitype:FilterInterface
     */
    protected $postFilters = array();

    /**
     * Call filters before recovering source
     */
    protected function preFilter()
    {
        foreach ($this->preFilters as $filter) {
            $filter->filter($this);
        }
    }

    /**
     * Call filters after recovering source
     */
    protected function postFilter()
    {
        foreach ($this->postFilters as $filter) {
            $filter->filter($this);
        }
    }

    /**
     * Add pre filter
     * @param FilterInterface $filter
     *
     * @return \Oro\Bundle\DataFlowBundle\Source\Source
     */
    public function addPreFilter(FilterInterface $filter)
    {
        $this->preFilters[$filter->getName()] = $filter;

        return $this;
    }

    /**
     * Remove a pre filter
     * @param FilterInterface $filter
     *
     * @return \Oro\Bundle\DataFlowBundle\Source\Source
     */
    public function removePreFilter(FilterInterface $filter)
    {
        if (isset($this->preFilters[$filter->getName()])) {
            unset($this->preFilters[$filter->getName()]);
        }

        return $this;
    }

    /**
     * Add a post filter
     * @param FilterInterface $filter
     *
     * @return \Oro\Bundle\DataFlowBundle\Source\Source
     */
    public function addPostFilter(FilterInterface $filter)
    {
        $this->postFilters[$filter->getName()] = $filter;

        return $this;
    }

    /**
     * Remove a post filter
     * @param FilterInterface $filter
     *
     * @return \Oro\Bundle\DataFlowBundle\Source\Source
     */
    public function removePostFilter(FilterInterface $filter)
    {
        if (isset($this->postFilters[$filter->getName()])) {
            unset($this->postFilters[$filter->getName()]);
        }

        return $this;
    }

}
