<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Fixtures\Entity;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

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
