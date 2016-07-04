<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Behat\Context;

use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Mink\Element\NodeElement;

class FeatureContext extends RawMinkContext
{
    /**
     * @Then /^(?P<contactsCount>(?:|one|two|\d+)) contacts added to form$/
     */
    public function assertCountContactsAddedToForm($contactsCount)
    {
        \PHPUnit_Framework_Assert::assertCount($this->getCount($contactsCount), $this->getFormContacts());
    }

    /**
     * @When /^(?:|I should )see (?P<contactsCount>(?:|one|two|\d+)) contact(?:|s)$/
     */
    public function assertCountOfContacts($contactsCount)
    {
        \PHPUnit_Framework_Assert::assertCount(
            $this->getCount($contactsCount),
            $this->getSession()->getPage()->findAll('css', '.contact-box')
        );
    }

    /**
     * @When :name should be default contact
     */
    public function assertDefaultContact($name)
    {
        $contactBoxes = $this->getSession()->getPage()->findAll('css', '.contact-box');

        /** @var NodeElement $box */
        foreach ($contactBoxes as $box) {
            if (false !== strpos($box->getText(), $name)) {
                \PHPUnit_Framework_Assert::assertRegExp('/Default Contact/i', $box->getText());
                return;
            }
        }

        throw new ExpectationException(
            sprintf('Can\'t find contact with "%s" name', $name),
            $this->getSession()->getDriver()
        );
    }


    /**
     * @Then /^(?:|I )select ([\w\s]*) contact as default$/
     */
    public function selectContactAsDefault($name)
    {
        foreach ($this->getFormContacts() as $contact) {
            if (false !== strpos($contact->getText(), $name)) {
                $contact->find('css', 'input[type="radio"]')->click();

                return;
            }
        }

        throw new ExpectationException(
            sprintf('Can\'t find contact with "%s" name', $name),
            $this->getSession()->getDriver()
        );
    }

    /**
     * @Then delete :name contact
     */
    public function deleteContact($name)
    {
        foreach ($this->getFormContacts() as $contact) {
            if (false !== strpos($contact->getText(), $name)) {
                $contact->find('css', 'i.icon-remove')->click();

                return;
            }
        }

        throw new ExpectationException(
            sprintf('Can\'t find contact with "%s" name', $name),
            $this->getSession()->getDriver()
        );
    }

    /**
     * @return NodeElement[]
     */
    protected function getFormContacts()
    {
        $page = $this->getSession()->getPage();

        return $page->findAll('css', 'div[id^="orocrm_account_form_contacts"] .list-group-item');
    }

    /**
     * @param int|string $count
     * @return int
     */
    protected function getCount($count)
    {
        switch (trim($count)) {
            case '':
                return 1;
            case 'one':
                return 1;
            case 'two':
                return 2;
            default:
                return (int) $count;
        }
    }
}
