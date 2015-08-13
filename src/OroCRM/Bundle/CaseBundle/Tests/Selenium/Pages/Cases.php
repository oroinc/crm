<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Cases
 *
 * @package OroCRM\Bundle\CaseBundle\Tests\Selenium\Pages
 * @method Cases openCases openCase(string)
 * @method CaseEntity add add()
 * @method CaseEntity open open()
 * {@inheritdoc}
 */
class Cases extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Create Case']";
    const URL = 'case';

    public function entityNew()
    {
        $case = new CaseEntity($this->test);
        return $case->init();
    }

    public function entityView()
    {
        return new CaseEntity($this->test);
    }
}
