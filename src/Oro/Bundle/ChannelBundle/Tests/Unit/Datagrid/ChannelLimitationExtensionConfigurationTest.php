<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Datagrid;

use Oro\Bundle\ChannelBundle\Datagrid\ChannelLimitationExtensionConfiguration;
use Symfony\Component\Config\Definition\Processor;

class ChannelLimitationExtensionConfigurationTest extends \PHPUnit\Framework\TestCase
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

    public function testInvalidRelationGiven()
    {
        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);
        $this->expectExceptionMessage('Must contains relative path with single nesting');

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
