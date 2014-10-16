<?php

namespace OroCRM\Bundle\CallBundle\Tests\Unit\Fixtures\Entity;

class TestTarget
{
    protected $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
