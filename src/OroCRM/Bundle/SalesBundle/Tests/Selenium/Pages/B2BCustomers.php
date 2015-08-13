<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class B2BCustomers
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method B2BCustomers openB2BCustomers openB2BCustomers(string)
 * @method B2BCustomer add add()
 * @method B2BCustomer open open()
 * {@inheritdoc}
 */
class B2BCustomers extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Create B2B customer']";
    const URL = 'b2bcustomer';

    public function entityNew()
    {
        return new B2BCustomer($this->test);
    }

    public function entityView()
    {
        return new B2BCustomer($this->test);
    }
}
