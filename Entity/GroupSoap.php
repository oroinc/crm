<?php

namespace Oro\Bundle\ContactBundle\Entity;

use JMS\Serializer\Annotation\Exclude;

class GroupSoap extends Group
{
    /**
     * @Exclude
     */
    protected $id;
}
