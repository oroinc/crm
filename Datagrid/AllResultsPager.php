<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\PagerInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\SearchBundle\Query\Result;

class AllResultsPager implements PagerInterface
{
    /**
     * @var int
     */
    protected $maxPerPage = 10;

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var int
     */
    protected $nbResults = 0;

    /**
     * @var Result
     */
    protected $queryResult;

    /**
     * @param Result $queryResult
     */
    public function setQuery($queryResult)
    {
        $this->queryResult = $queryResult;
    }

    /**
     * Initialize the Pager.
     */
    public function init()
    {
        if (!$this->queryResult) {
            throw new \LogicException('Indexer query result must be set');
        }

        $this->nbResults = $this->queryResult->getRecordsCount();
    }

    /**
     * Returns the number of results.
     *
     * @return integer
     */
    public function getNbResults()
    {
        return $this->nbResults;
    }

    /**
     * @param int $maxPerPage
     */
    public function setMaxPerPage($maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;
    }

    /**
     * @return int
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * @param int $page
     * @return void
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Returns the previous page.
     *
     * @return int
     */
    public function getPreviousPage()
    {
        return max($this->getPage() - 1, $this->getFirstPage());
    }

    /**
     * Returns the next page.
     *
     * @return integer
     */
    public function getNextPage()
    {
        return min($this->getPage() + 1, $this->getLastPage());
    }

    /**
     * Returns the first page number.
     *
     * @return integer
     */
    public function getFirstPage()
    {
        return 1;
    }

    /**
     * Returns the last page number.
     *
     * @return integer
     */
    public function getLastPage()
    {
        return ceil($this->getNbResults() / $this->getMaxPerPage());
    }

    /**
     * @return boolean
     */
    public function haveToPaginate()
    {
        return $this->getMaxPerPage() && $this->getNbResults() > $this->getMaxPerPage();
    }

    /**
     * Returns an array of page numbers to use in pagination links.
     *
     * @deprecated Should not be used
     *
     * @param integer $nbLinks The maximum number of page numbers to return
     * @return array
     */
    public function getLinks($nbLinks = null)
    {
        return array();
    }
}
