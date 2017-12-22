<?php

namespace Oro\Bundle\MagentoBundle\Entity;

interface CreatedAtAwareInterface
{
    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @param \DateTime $createdAt
     *
     * @return CreatedAtAwareInterface
     */
    public function setCreatedAt(\DateTime $createdAt = null);
}
