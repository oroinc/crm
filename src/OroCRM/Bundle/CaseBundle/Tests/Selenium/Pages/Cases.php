<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Cases
 *
 * @package OroCRM\Bundle\CaseBundle\Tests\Selenium\Pages
 * @method Cases openCases openCase(string)
 * {@inheritdoc}
 */
class Cases extends AbstractPageFilteredGrid
{
    const URL = 'case';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return CaseEntity
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Create Case']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();
        $task = new CaseEntity($this->test);
        return $task->init();
    }

    /**
     * @param array $entityData
     *
     * @return CaseEntity
     */
    public function open($entityData = array())
    {
        $task = $this->getEntity($entityData, 1);
        $task->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new CaseEntity($this->test);
    }
}
