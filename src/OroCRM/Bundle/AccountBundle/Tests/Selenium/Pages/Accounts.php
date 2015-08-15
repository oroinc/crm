<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Accounts
 *
 * @package OroCRM\Bundle\AccountBundle\Tests\Selenium\Pages
 * @method Accounts openAccounts(string $bundlePath)
 * @method Account add()
 * @method Account open(array $filter)
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
