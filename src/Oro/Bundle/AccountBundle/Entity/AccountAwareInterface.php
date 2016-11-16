<?php

namespace Oro\Bundle\AccountBundle\Entity;

interface AccountAwareInterface
{
    /**
     * @return Account
     */
    public function getAccount();
}
