<?php

namespace OroCRM\Bundle\CallBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Calls
 * @package OroCRM\Bundle\CallBundle\Tests\Selenium\Pages
 * @method Calls openCalls openCalls(string)
 * @method Call add add()
 * @method Call open open()
 * {@inheritdoc}
 */
class Calls extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Log call']";
    const URL = 'call';

    public function entityNew()
    {
        return new Call($this->test);
    }

    public function entityView()
    {
        return new Call($this->test);
    }
}
