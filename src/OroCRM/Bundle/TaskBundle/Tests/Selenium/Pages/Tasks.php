<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Tasks
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method Tasks openTasks openTasks(string)
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
        $task = new Task($this->test);
        return $task->init();
    }

    /**
     * @param array $entityData
     *
     * @return mixed|Task
     */
    public function open($entityData = array())
    {
        $task = $this->getEntity($entityData, 1);
        $task->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Task($this->test);
    }
}
