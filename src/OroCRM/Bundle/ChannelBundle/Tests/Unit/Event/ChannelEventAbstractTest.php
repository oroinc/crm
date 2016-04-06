<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Event;


abstract class ChannelEventAbstractTest extends \PHPUnit_Framework_TestCase
{

    const PHP_VERSION_7 = 7;

    /**
     * Return php version
     *
     * @return int|null
     */
    protected function getPhpVersion()
    {
        $version = explode('.', phpversion());

        $version = (isset($version[0])) ? $version[0] : null;

        return $version;
    }
}