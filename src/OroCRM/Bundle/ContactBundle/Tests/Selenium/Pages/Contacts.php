<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Contacts
 *
 * @package OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages
 * @method Contacts openContacts openContacts(string)
 * {@inheritdoc}
 */
class Contacts extends AbstractPageFilteredGrid
{
    const URL = 'contact';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return Contact
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Create Contact']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $contact = new Contact($this->test);
        return $contact->init();
    }

    public function open($entityData = array())
    {
        $contact = $this->getEntity($entityData);
        $contact->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Contact($this->test);
    }
}
