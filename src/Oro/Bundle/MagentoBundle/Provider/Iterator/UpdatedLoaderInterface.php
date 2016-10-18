<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator;

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
     * Returns date from what read was started
     *
     * @return \DateTime
     */
    public function getStartDate();

    /**
     * Set mode
     *
     * @param string $mode
     */
    public function setMode($mode);

    /**
     * @param \DateInterval $syncRange
     */
    public function setSyncRange(\DateInterval $syncRange);

    /**
     * @param \DateTime $date
     * @return mixed
     */
    public function setMinSyncDate(\DateTime $date);

    /**
     * @param array $entitiesIdsBuffer
     */
    public function setEntitiesIdsBuffer(array $entitiesIdsBuffer);
}
