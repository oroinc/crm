<?php

namespace Oro\Bundle\TestsBundle\Pages\BAP;

use Oro\Bundle\TestsBundle\Pages\PageFilteredGrid;

class Users extends PageFilteredGrid
{
    const URL = 'user';

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
        $user = new User($this->test);
        return $user->init(true);
    }

    public function open($entityData = array())
    {
        $user = $this->getEntity($entityData);
        $user->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new User($this->test);
    }
}
