<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Accounts
 *
 * @package OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages
 * @method Accounts openAccounts openAccounts(string)
 * {@inheritdoc}
 */
class Accounts extends AbstractPageFilteredGrid
{
    const URL = 'account';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return Account
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Create Account']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $account = new Account($this->test);
        return $account->init();
    }

    public function open($entityData = array())
    {
        $contact = $this->getEntity($entityData);
        $contact->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Account($this->test);
    }
}
