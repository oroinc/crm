<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Fixtures\Entity;

use Oro\Bundle\ContactBundle\Entity\Contact;

class ExtendContact extends Contact
{
    /**
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
