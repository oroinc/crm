<?php
declare(strict_types=1);

namespace Oro\Bundle\AccountBundle\Event;

/**
 * Allows the subscribed listeners to provide customers associated with a certain account.
 */
class CollectAccountWebsiteActivityCustomersEvent
{
    /** @var int */
    private $accountId;

    /** @var array */
    private $customers = [];

    public function __construct(int $accountId)
    {
        $this->accountId = $accountId;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getCustomers(): array
    {
        return $this->customers;
    }

    public function setCustomers(array $customers): void
    {
        $this->customers = $customers;
    }
}
