<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Channel
 * @package OroCRM\Bundle\ChannelBundle\Tests\Selenium\Pages
 * @method Channels openChannels openChannels(string)
 * @method Channel add add()
 * @method Channel open open()
 * {@inheritdoc}
 */
class Channels extends AbstractPageFilteredGrid
{
    const NEW_ENTITY_BUTTON = "//a[@title='Create Channel']";
    const URL = 'channel';

    public function entityNew()
    {
        return new Channel($this->test);
    }

    public function entityView()
    {
        return new Channel($this->test);
    }
}
