<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

interface DataIteratorInterface extends \Iterator
{
    const ALIAS_GROUPS   = 'groups';
    const ALIAS_STORES   = 'stores';
    const ALIAS_WEBSITES = 'websites';
    const ALIAS_REGIONS  = 'regions';

    const IMPORT_MODE_INITIAL = 'initial';
    const IMPORT_MODE_UPDATE  = 'update';

    /**
     * Set start date for read from
     *
     * @param \DateTime $date
     */
    public function setStartDate(\DateTime $date);

    /**
     * Set mode
     *
     * @param int $mode
     */
    public function setMode($mode);
}
