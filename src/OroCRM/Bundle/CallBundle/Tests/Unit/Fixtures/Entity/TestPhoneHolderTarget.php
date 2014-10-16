<?php

namespace OroCRM\Bundle\CallBundle\Tests\Unit\Fixtures\Entity;

use Oro\Bundle\AddressBundle\Model\PhoneHolderInterface;

class TestPhoneHolderTarget implements PhoneHolderInterface
{
    protected $id;
    protected $phones;

    public function __construct($id = null, array $phones = [])
    {
        $this->id     = $id;
        $this->phones = $phones;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPrimaryPhoneNumber()
    {
        return !empty($this->phones) ? $this->phones[0] : null;
    }

    public function getPhoneNumbers()
    {
        return $this->phones;
    }
}
