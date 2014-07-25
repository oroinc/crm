<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

interface UpdatedLoaderInterface extends \Iterator
{
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
     * @param string $mode
     */
    public function setMode($mode);

    /**
     * @return string
     */
    public function getMode();
}
