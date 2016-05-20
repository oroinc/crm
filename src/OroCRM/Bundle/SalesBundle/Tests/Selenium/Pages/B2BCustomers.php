<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class B2BCustomers
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method B2BCustomers openB2BCustomers(string $bundlePath)
 * @method B2BCustomer add()
 * @method B2BCustomer open(array $filter)
 * {@inheritdoc}
 */
class B2BCustomers extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Create Business Customer']";
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
