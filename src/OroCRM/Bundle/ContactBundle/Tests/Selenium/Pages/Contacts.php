<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Contacts
 *
 * @package OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages
 * @method Contacts openContacts openContacts(string)
 * @method Contact add add()
 * @method Contact open open()
 * {@inheritdoc}
 */
class Contacts extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Create Contact']";
    const URL = 'contact';

    public function entityNew()
    {
        $contact = new Contact($this->test);
        return $contact->init();
    }

    public function entityView()
    {
        return new Contact($this->test);
    }
}
