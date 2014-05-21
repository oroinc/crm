<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

abstract class AbstractController extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(array('debug' => false), $this->generateBasicAuthHeader());

        $this->loadFixtures(
            array(
                'OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel',
            )
        );
    }

    /**
     * @dataProvider gridProvider
     *
     * @param array $filters
     */
    public function testGrid($filters)
    {
        $this->client->requestGrid($filters['gridParameters'], $filters['gridFilters']);
        $result = $this->client->getResponse();
        $this->assertTrue($result->isSuccessful());
        $this->assertTrue($result->isOk());
        $data = json_decode($result->getContent(), 1);
        $count = 0;

        foreach ($data['data'] as $grid) {
            if ($filters['channelName'] === $grid['channelName']) {
                ++$count;
                foreach ($filters['verifying'] as $fieldName => $value) {
                    $this->assertEquals($value, $grid[$fieldName]);
                }
                break;
            }
        }

        if ($filters['oneOrMore']) {
            $this->assertGreaterThanOrEqual($count, 1);
        } else {
            $this->assertEquals($count, 0);
        }
    }
}
