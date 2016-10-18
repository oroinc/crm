<?php

namespace Oro\Bundle\ChannelBundle\Tests\Selenium\Pages;

use Oro\Bundle\TestFrameworkBundle\Pages\AbstractPageFilteredGrid;

/**
 * Class Channel
 * @package Oro\Bundle\ChannelBundle\Tests\Selenium\Pages
 * @method Channels openChannels(string $bundlePath)
 * @method Channel add()
 * @method Channel open(array $filter)
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
