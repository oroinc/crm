<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Sonata\AdminBundle\Datagrid\PagerInterface as BasePagerInterface;

interface PagerInterface extends BasePagerInterface
{
    /**
     * @param ProxyQueryInterface $query
     * @return void
     */
    public function setQuery($query);

    /**
     * @param int $maxPerPage
     * @return void
     */
    public function setMaxPerPage($maxPerPage);

    /**
     * @return int
     */
    public function getMaxPerPage();

    /**
     * @param int $page
     * @return void
     */
    public function setPage($page);

    /**
     * @return int
     */
    public function getPage();
}
