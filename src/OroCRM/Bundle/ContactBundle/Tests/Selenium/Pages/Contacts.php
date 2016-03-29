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
        $gridHeader = "//thead[contains(@class, 'grid-header')][not(contains(@class, 'thead-sizing'))]";
        if ($this->isElementPresent("{$gridHeader}//*[@title='Mass Actions']")) {
            $massActions = $this->test->byXPath("{$gridHeader}//*[@title='Mass Actions']");
            if ($massActions->displayed()) {
                $this->test->byXPath(
                    "{$gridHeader}//button[@class='btn btn-default btn-small dropdown-toggle' and not(@type)]"
                )->click();
                $this->waitForAjax();
                $this->test->byXPath(
                    "//ul[contains(@class,'dropdown-menu__select-all-header-cell')]" .
                    "[contains(@class,'dropdown-menu__floating')]//a[text() ='All']"
                )->click();
                $this->waitForAjax();
                $massActions->click();
                $this->waitForAjax();
                $this->test->byXPath(
                    "//ul[contains(@class,'dropdown-menu__action-column')]" .
                    "[contains(@class,'dropdown-menu__floating')]//a[@title ='Delete']"
                )->click();
                $this->waitForAjax();
                $this->test
                    ->byXpath("//div[div[contains(., 'Delete Confirmation')]]//a[text()='Yes, Delete']")
                    ->click();
                $this->waitForAjax();
                $this->waitPageToLoad();
            }
        }
        return $this;
    }
}
