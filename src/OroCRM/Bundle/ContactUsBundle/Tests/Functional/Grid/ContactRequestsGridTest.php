<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Functional\Grid;

use OroCRM\Bundle\ContactUsBundle\Tests\Functional\Fixtures\LoadContactUsBundleFixtures;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ContactRequestsGridTest extends AbstractGrid
{
    /**
     * @return array
     */
    public function gridProvider()
    {
        return [
            'B2B Customer grid'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'orcrm-contact-requests-grid'
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
                        'channelName'  => LoadContactUsBundleFixtures::CHANNEL_NAME,
                        'firstName'    => 'fname',
                        'lastName'     => 'lname',
                        'emailAddress' => 'email@email.com',
                        'phone'        => '123123123'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'B2B Customer grid with filters'   => [
                [
                    'gridParameters'      => [
                        'gridName' => 'orcrm-contact-requests-grid'
                    ],
                    'gridFilters'         => [
                        'orcrm-contact-requests-grid[_filter][channelName][value]' =>
                            LoadContactUsBundleFixtures::CHANNEL_NAME,
                        'orcrm-contact-requests-grid[_filter][firstName][value]'   => 'fname'
                    ],
                    'assert'              => [
                        'channelName'  => LoadContactUsBundleFixtures::CHANNEL_NAME,
                        'firstName'    => 'fname',
                        'lastName'     => 'lname',
                        'emailAddress' => 'email@email.com',
                        'phone'        => '123123123'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'B2B Customer grid without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'orcrm-contact-requests-grid'
                    ],
                    'gridFilters'         => [
                        'orcrm-contact-requests-grid[_filter][channelName][value]' =>
                            LoadContactUsBundleFixtures::CHANNEL_NAME,
                        'orcrm-contact-requests-grid[_filter][firstName][value]'   => 'something'
                    ],
                    'assert'              => [
                        'channelName'  => LoadContactUsBundleFixtures::CHANNEL_NAME,
                        'firstName'    => 'fname',
                        'lastName'     => 'lname',
                        'emailAddress' => 'email@email.com',
                        'phone'        => '123123123'
                    ],
                    'expectedResultCount' => 0
                ],
            ],
        ];
    }
}
