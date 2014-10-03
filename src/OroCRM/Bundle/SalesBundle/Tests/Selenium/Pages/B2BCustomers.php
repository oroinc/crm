<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class B2BCustomers
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method B2BCustomers openB2BCustomers openB2BCustomers(string)
 * {@inheritdoc}
 */
class B2BCustomers extends AbstractPageFilteredGrid
{
    const URL = 'b2bcustomer';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return B2BCustomer
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Create B2B customer']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new B2BCustomer($this->test);
    }

    public function open($entityData = array())
    {
        $page = parent::open($entityData);

        return new B2BCustomer($page->test);
    }
}
