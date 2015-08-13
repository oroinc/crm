<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Accounts
 *
 * @package OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages
 * @method Accounts openAccounts openAccounts(string)
 * @method Account add add()
 * @method Account open open()
 * {@inheritdoc}
 */
class Accounts extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Create Account']";
    const URL = 'account';

    public function entityNew()
    {
        return new Account($this->test);
    }

    public function entityView()
    {
        return new Account($this->test);
    }
}
