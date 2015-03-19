<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Tasks
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method Tasks openTasks openTasks(string)
 *
 * {@inheritdoc}
 */
class Tasks extends AbstractPageFilteredGrid
{
    const URL = 'task';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return Task
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Create Task']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        return new Task($this->test);
    }

    /**
     * @param array $entityData
     *
     * @return Task
     */
    public function open($entityData = array())
    {
        $page = parent::open($entityData);

        return new Task($page->test);
    }
}
