<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\Command;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\AnalyticsBundle\Command\CalculateAnalyticsCommand;

/**
 * @dbIsolation
 */
class CalculateAnalyticsCommandTest extends WebTestCase
{
    /**
     * @param array $parameters
     * @param array $expects
     * @param array $notContains
     *
     * @dataProvider dataProvider
     */
    public function testCommand(array $parameters, array $expects, array $notContains = [])
    {
        $this->resetClient();
        $this->initClient();

        $this->loadFixtures(['OroCRM\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures\LoadEntitiesData'], true);

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
                [
                    'CustomerIdentityChannel skipped',
                    'OroCRM\Bundle\ChannelBundle\Entity\CustomerIdentity does not implements',
                ],
                [
                    'processing',
                    'OroCRM\Bundle\MagentoBundle\Entity\Customer #',
                ]
            ],
            'supported channel' => [
                [
                    '--channel' => 'Channel.CustomerChannel',
                ],
                [
                    'CustomerChannel processing',
                    'Done. 2/2 updated',
                ],
                [
                    'skipped',
                    'OroCRM\Bundle\MagentoBundle\Entity\Customer #',
                ]
            ],
            'verbose supported' => [
                [
                    '--channel' => 'Channel.CustomerChannel',
                    '-v' => true,
                ],
                [
                    'CustomerChannel processing',
                    'OroCRM\Bundle\MagentoBundle\Entity\Customer #',
                    'Done. 2/2 updated',
                ],
                [
                    'skipped',
                ]
            ],
            'non existing id' => [
                [
                    '--channel' => 'Channel.CustomerChannel',
                    '--ids' => 42,
                ],
                [
                    'CustomerChannel processing',
                    'Done. 0/0 updated',
                ],
                [
                    'skipped',
                    'OroCRM\Bundle\MagentoBundle\Entity\Customer #',
                ]
            ],
            'existing entity' => [
                [
                    '--channel' => 'Channel.CustomerChannel',
                    '--ids' => 'Channel.CustomerChannel.Customer',
                ],
                [
                    'CustomerChannel processing',
                    'Done. 1/1 updated',
                ],
                [
                    'skipped',
                    'OroCRM\Bundle\MagentoBundle\Entity\Customer #',
                ]
            ],
            'ids with wrong channel' => [
                [
                    '--channel' => 'Channel.CustomerIdentity',
                    '--ids' => 'Channel.CustomerChannel.Customer',
                ],
                [
                    'CustomerIdentityChannel skipped',
                    'OroCRM\Bundle\ChannelBundle\Entity\CustomerIdentity does not implements',
                ],
                [
                    'processing',
                    'OroCRM\Bundle\MagentoBundle\Entity\Customer #',
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
                    'processing',
                    'skipped',
                    'OroCRM\Bundle\MagentoBundle\Entity\Customer #',
                ]
            ],
            'all channels' => [
                [],
                [
                    'CustomerIdentityChannel skipped',
                    'OroCRM\Bundle\ChannelBundle\Entity\CustomerIdentity does not implements',
                    'CustomerChannel processing',
                    'Done. 2/2 updated',
                ],
                [
                    'OroCRM\Bundle\MagentoBundle\Entity\Customer #',
                ]
            ],
            'all channels with verbose' => [
                [
                    '-v' => true,
                ],
                [
                    'CustomerIdentityChannel skipped',
                    'OroCRM\Bundle\ChannelBundle\Entity\CustomerIdentity does not implements',
                    'CustomerChannel processing',
                    'Done. 2/2 updated',
                ]
            ]
        ];
    }
}
