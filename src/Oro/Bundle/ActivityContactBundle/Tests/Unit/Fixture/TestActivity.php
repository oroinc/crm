<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\Fixture;

class TestActivity
{
    /** @var string */
    protected $direction;

    /** @var \DateTime */
    protected $created;

    /**
     * @param string    $direction
     * @param \DateTime $created
     */
    public function __construct($direction, \DateTime $created)
    {
        $this->direction = $direction;
        $this->created = $created;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }
}
