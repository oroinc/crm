<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Channel
 * @package OroCRM\Bundle\ChannelBundle\Tests\Selenium\Pages
 * @method Channels openChannels openChannels(string)
 * {@inheritdoc}
 */
class Channels extends AbstractPageFilteredGrid
{
    const URL = 'channel';

    public function __construct($testCase, $redirect = true)
    {
        $this->redirectUrl = self::URL;
        parent::__construct($testCase, $redirect);
    }

    /**
     * @return Channel
     */
    public function add()
    {
        $this->test->byXPath("//a[@title='Create Channel']")->click();
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Channel($this->test);
    }

    /**
     * @param array $entityData
     * @return Channel
     */
    public function open($entityData = array())
    {
        $cart = $this->getEntity($entityData, 3);
        $cart->click();
        sleep(1);
        $this->waitPageToLoad();
        $this->waitForAjax();

        return new Channel($this->test);
    }
}
