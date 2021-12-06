<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Functional\Grid;

use Oro\Bundle\ContactUsBundle\Tests\Functional\Fixtures\LoadContactUsBundleFixtures;
use Oro\Bundle\DataGridBundle\Tests\Functional\AbstractDatagridTestCase;

class ContactRequestsGridTest extends AbstractDatagridTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadContactUsBundleFixtures::class]);
    }

    /**
     * {@inheritdoc}
     */
    public function gridProvider(): array
    {
        return [
            'Contact Request grid'                => [
                [
                    'gridParameters'      => [
                        'gridName' => 'orcrm-contact-requests-grid'
                    ],
                    'gridFilters'         => [],
                    'assert'              => [
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
