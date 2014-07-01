<?php

namespace OroCRM\Bundle\CallBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Calls
 * @package OroCRM\Bundle\CallBundle\Tests\Selenium\Pages
 * @method Calls openCalls openCalls(string)
 * {@inheritdoc}
 */
class Calls extends AbstractPageFilteredGrid
{
    const URL = 'call';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return Call
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Log call']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Call($this->test);
    }

    /**
     * @param array $entityData
     * @return Call
     */
    public function open($entityData = array())
    {
        $cart = $this->getEntity($entityData, 3);
        $cart->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Call($this->test);
    }
}
