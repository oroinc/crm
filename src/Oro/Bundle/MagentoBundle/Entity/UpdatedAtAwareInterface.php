<?php

namespace Oro\Bundle\MagentoBundle\Entity;

interface UpdatedAtAwareInterface
{
    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime $updatedAt
     *
     * @return UpdatedAtAwareInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);
}
