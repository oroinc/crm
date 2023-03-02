<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\Stub;

use Oro\Bundle\AccountBundle\Entity\Account;

/**
 * Account stub for testing purpose
 */
class AccountStub extends Account
{
    protected $ac_last_contact_date;
    protected $ac_last_contact_date_out;
    protected $ac_last_contact_date_in;

    public function getCreated(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreated(\DateTime $date): void
    {
        $this->createdAt = $date;
    }

    /**
     * @return mixed
     */
    public function getAcLastContactDate()
    {
        return $this->ac_last_contact_date;
    }

    /**
     * @param mixed $ac_last_contact_date
     */
    public function setAcLastContactDate($ac_last_contact_date): void
    {
        $this->ac_last_contact_date = $ac_last_contact_date;
    }

    /**
     * @return mixed
     */
    public function getAcLastContactDateOut()
    {
        return $this->ac_last_contact_date_out;
    }

    /**
     * @param mixed $ac_last_contact_date_out
     */
    public function setAcLastContactDateOut($ac_last_contact_date_out): void
    {
        $this->ac_last_contact_date_out = $ac_last_contact_date_out;
    }

    /**
     * @return mixed
     */
    public function getAcLastContactDateIn()
    {
        return $this->ac_last_contact_date_in;
    }

    /**
     * @param mixed $ac_last_contact_date_in
     */
    public function setAcLastContactDateIn($ac_last_contact_date_in): void
    {
        $this->ac_last_contact_date_in = $ac_last_contact_date_in;
    }
}
