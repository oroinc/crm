<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Contacts
 *
 * @package OroCRM\Bundle\ContactBundle\Tests\Selenium\Pages
 * @method Contacts openContacts(string $bundlepath)
 * @method Contact add()
 * @method Contact open(array $filter)
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

    /**
     * Method check if grid of Contacts not empty and do mass delete
     * @return $this
     */
    public function massDelete()
    {
        $massActions = $this->test->byXPath("//thead[not(@class)]//*[@title='Mass Actions']");
        $this->test->byXPath(
            "//thead[not(@class)]//button[@class='btn btn-default btn-small dropdown-toggle' and not(@type)]"
        )->click();
        $this->waitForAjax();
        $this->test->byXPath(
            "//div[@class='grid-container']//div[@class='btn-group dropdown open']//a[text() ='All']"
        )->click();
        $this->waitForAjax();
        $massActions->click();
        $this->waitForAjax();
        $this->test->byXPath(
            "//div[@class='grid-container']//div[@class='dropdown btn-group open']//a[@title ='Delete']"
        )->click();
        $this->waitForAjax();
        $this->test
            ->byXPath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")
            ->click();
        $this->waitForAjax();
        $this->waitPageToLoad();
        return $this;
    }
}
