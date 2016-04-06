<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Event;


abstract class ChannelEventAbstractTest extends \PHPUnit_Framework_TestCase
{
    protected function getExpectedExceptionCode()
    {
        return version_compare(PHP_VERSION, '7.0.0', '>=') ? 'PHPUnit_Framework_Error' : 'TypeError';
    }
}