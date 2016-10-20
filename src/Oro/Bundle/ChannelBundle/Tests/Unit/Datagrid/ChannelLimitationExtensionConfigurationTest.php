<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Datagrid;

use Symfony\Component\Config\Definition\Processor;

use Oro\Bundle\ChannelBundle\Datagrid\ChannelLimitationExtensionConfiguration;

class ChannelLimitationExtensionConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfiguration()
    {
        $this->assertSame(['channel_relation_path' => '.dataChannel'], $this->processConfiguration([]));
    }

    public function testGivenSomeValidConfiguration()
    {
        $resolved = $this->processConfiguration(['root' => ['channel_relation_path' => '.channel']]);

        $this->assertSame(['channel_relation_path' => '.channel'], $resolved);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Must contains relative path with single nesting
     */
    public function testInvalidRelationGiven()
    {
        $this->processConfiguration(['root' => ['channel_relation_path' => '.entity.channel']]);
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function processConfiguration(array $config)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new ChannelLimitationExtensionConfiguration(), $config);
    }
}
