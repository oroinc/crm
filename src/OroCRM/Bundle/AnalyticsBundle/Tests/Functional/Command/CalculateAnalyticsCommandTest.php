<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\Command;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\AnalyticsBundle\Command\CalculateAnalyticsCommand;

/**
 * @dbIsolation
 */
class CalculateAnalyticsCommandTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], [], true);
        $this->loadFixtures(['OroCRM\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures\LoadEntitiesData'], true);
    }

    /**
     * @param array $parameters
     * @param array $expects
     * @param array $notContains
     *
     * @dataProvider dataProvider
     */
    public function testCommand(array $parameters, array $expects = [], array $notContains = [])
    {
        $options = ['--ids', '--channel'];
        foreach ($options as $option) {
            if (!empty($parameters[$option])) {
                if (is_string($parameters[$option])) {
                    $parameters[$option] = $this->getReference($parameters[$option])->getId();
                }

                $parameters[] = sprintf('%s=%s', $option, $parameters[$option]);

                unset($parameters[$option]);
            }
        }

        $output = $this->runCommand(CalculateAnalyticsCommand::COMMAND_NAME, $parameters);
        foreach ($expects as $expect) {
            $this->assertContains($expect, $output);
        }
        foreach ($notContains as $notContain) {
            $this->assertNotContains($notContain, $output);
        }
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProvider()
    {
        return [
            'one not supported channel' => [
                [
                    '--channel' => 'Channel.CustomerIdentity',
                ],
                [],
                [
                    '[Process]',
                    '[Done]',
                ]
            ],
            'supported channel' => [
                [
                    '--channel' => 'Channel.CustomerChannel',
                ],
                [
                    '[Process] Channel: CustomerChannel',
                    '[Done] Channel: CustomerChannel updated.',
                ]
            ],
            'non existing id' => [
                [
                    '--channel' => 'Channel.CustomerChannel',
                    '--ids' => 42,
                ],
                [
                    '[Process] Channel: CustomerChannel',
                    '[Done] Channel: CustomerChannel updated.',
                ]
            ],
            'existing entity' => [
                [
                    '--channel' => 'Channel.CustomerChannel',
                    '--ids' => 'Channel.CustomerChannel.Customer',
                ],
                [
                    '[Process] Channel: CustomerChannel',
                    '[Done] Channel: CustomerChannel updated.',
                ]
            ],
            'ids with wrong channel' => [
                [
                    '--channel' => 'Channel.CustomerIdentity',
                    '--ids' => 'Channel.CustomerChannel.Customer',
                ],
                [],
                [
                    '[Process]',
                    '[Done]',
                ]
            ],
            'all channels with ids' => [
                [
                    '--ids' => 'Channel.CustomerChannel.Customer',
                ],
                [
                    'Option "ids" does not work without "channel"',
                ],
                [
                    '[Process]',
                    '[Done]',
                ]
            ],
            'all channels' => [
                [],
                [
                    '[Process] Channel: CustomerChannel',
                    '[Done] Channel: CustomerChannel updated.',
                    '[Process] Channel: CustomerChannel2',
                    '[Done] Channel: CustomerChannel2 updated.',
                ]
            ]
        ];
    }
}
