<?php

namespace Oro\Bundle\AccountBundle\Entity;

interface AccountAwareInterface
{
    /**
     * @return Account|null
     */
    public function getAccount();
}
