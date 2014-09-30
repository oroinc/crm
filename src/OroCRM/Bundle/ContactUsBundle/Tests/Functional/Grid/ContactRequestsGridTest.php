<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Functional\Grid;

use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;

use OroCRM\Bundle\ContactUsBundle\Tests\Functional\Fixtures\LoadContactUsBundleFixtures;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ContactRequestsGridTest extends AbstractDatagridTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures(['OroCRM\Bundle\ContactUsBundle\Tests\Functional\Fixtures\LoadContactUsBundleFixtures']);
    }

    /**
     * @return array
     */
    public function gridProvider()
    {
        return [
            'Contact Request grid'                => [
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
            'Contact Request grid with filters'   => [
                [
                    'gridParameters'      => [
                        'gridName' => 'orcrm-contact-requests-grid'
                    ],
                    'gridFilters'         => [
                        'orcrm-contact-requests-grid[_filter][firstName][value]' => 'fname'
                    ],
                    'assert'              => [
                        'firstName'    => 'fname',
                        'lastName'     => 'lname',
                        'emailAddress' => 'email@email.com',
                        'phone'        => '123123123'
                    ],
                    'expectedResultCount' => 1
                ],
            ],
            'Contact Request grid without result' => [
                [
                    'gridParameters'      => [
                        'gridName' => 'orcrm-contact-requests-grid'
                    ],
                    'gridFilters'         => [
                        'orcrm-contact-requests-grid[_filter][firstName][value]' => 'something'
                    ],
                    'assert'              => [],
                    'expectedResultCount' => 0
                ],
            ],
        ];
    }
}
