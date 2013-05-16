<?php

namespace Oro\Bundle\TestsBundle\Pages\BAP;

use Oro\Bundle\TestsBundle\Pages\PageFilteredGrid;

class Roles extends PageFilteredGrid
{
    const URL = 'user/role';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);

    }

    public function add()
    {
        $this->test->byXPath("//a[contains(., 'Add new')]")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new Role($this->test);
    }
}
