<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Fixture;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Entity\AccountAwareInterface;

class AccountAwareCustomerTarget implements AccountAwareInterface
{
    /** @var int */
    protected $id;

    /** @var Account|null */
    protected $account;

    /**
     * @param int $id
     */
    public function __construct($id, Account $account = null)
    {
        $this->id = $id;
        $this->account = $account;
    }

    /**
     * @return Account|null
     */
    public function getAccount()
    {
        return $this->account;
    }
}
