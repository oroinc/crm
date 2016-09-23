<?php

namespace Oro\Bundle\MagentoBundle\Entity;

interface OriginAwareInterface
{
    /**
     * @param int $originId
     *
     * @return OriginAwareInterface
     */
    public function setOriginId($originId);

    /**
     * @return int
     */
    public function getOriginId();
}
