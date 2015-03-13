<?php

namespace OroCRM\Bundle\MagentoBundle\Entity;

interface OriginAwareInterface
{
    /**
     * @return string
     */
    public function getOriginId();

    /**
     * @param string $originId
     * @return string
     */
    public function setOriginId($originId);
}
