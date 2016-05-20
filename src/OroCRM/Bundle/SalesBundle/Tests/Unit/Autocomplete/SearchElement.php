<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Autocomplete;

class SearchElement
{
    /** @var int */
    protected $id;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getRecordId()
    {
        return $this->id;
    }
}
