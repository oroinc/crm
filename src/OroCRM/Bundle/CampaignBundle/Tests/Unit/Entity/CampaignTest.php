<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Entity;

use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\CampaignBundle\Entity\Campaign;

class CampaignTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\CampaignBundle\Entity\Campaign';
    }

    /**
     * @return array
     */
    public function getSetDataProvider()
    {
        $name        = 'Some Name';
        $code        = '123-abc';
        $date        = new \DateTime('now');
        $description = 'some description';
        $budget      = 10.44;
        $owner       = new User();

        return [
            'name'        => ['name', $name, $name],
            'code'        => ['code', $code, $code],
            'startDate'   => ['startDate', $date, $date],
            'endDate'     => ['endDate', $date, $date],
            'description' => ['description', $description, $description],
            'budget'      => ['budget', $budget, $budget],
            'owner'       => ['owner', $owner, $owner],
        ];
    }

    public function testDates()
    {
        $campaign = new Campaign();
        $testDate = new \DateTime('now', new \DateTimeZone('UTC'));

        $campaign->prePersist();
        $campaign->preUpdate();

        $this->assertEquals($testDate->format('Y-m-d'), $campaign->getCreatedAt()->format('Y-m-d'));
        $this->assertEquals($testDate->format('Y-m-d'), $campaign->getUpdatedAt()->format('Y-m-d'));
    }
}
