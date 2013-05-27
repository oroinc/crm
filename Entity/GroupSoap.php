<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use JMS\Serializer\Annotation\Exclude;

class GroupSoap extends Group
{
    /**
     * @Exclude
     */
    protected $id;
}
