<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Tasks
 *
 * @package OroCRM\Bundle\SalesBundle\Tests\Selenium\Pages
 * @method Tasks openTasks openTasks(string)
 * @method Task add add()
 * @method Task open open()
 *
 * {@inheritdoc}
 */
class Tasks extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Create Task']";
    const URL = 'task';

    public function entityNew()
    {
        return new Task($this->test);
    }

    public function entityView()
    {
        return new Task($this->test);
    }
}
