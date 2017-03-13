<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadClosedOpportunityFixtures;

class RestJsonApiTest extends RestJsonApiTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadFixtures([
            LoadClosedOpportunityFixtures::class,
        ]);
    }

    public function testGetOpportunities()
    {
        $response = $this->request(
            'GET',
            $this->getUrl('oro_rest_api_cget', ['entity' => 'opportunities'])
        );
        $this->assertApiResponseStatusCodeEquals($response, 200, 'opportunities', 'get_list');

        $this->assertResponseContains($this->fixturePath('get_list_opportunities.yml'), $response);
    }

    public function testPatchOpportunitiesAccount()
    {
        $opportunity = $this->getReference('lost_opportunity');
        
        $account = (new Account())
            ->setName('Orphan account');
        $this->getReferenceRepository()->setReference('orphan_account', $account);

        $this->getEntityManager()->persist($account);
        $this->getEntityManager()->flush();

        $response = $this->request(
            'PATCH',
            $this->getUrl('oro_rest_api_patch', ['entity' => 'opportunities', 'id' => $opportunity->getId()]),
            [
                'data' => [
                    'id' => (string) $opportunity->getId(),
                    'type' => 'opportunities',
                    'relationships' => [
                        'account' => [
                            'data' => [
                                'type' => 'accounts',
                                'id' => (string) $account->getId(),
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->assertApiResponseStatusCodeEquals($response, 200, 'opportunities', 'update');

        $this->assertResponseContains($this->fixturePath('patch_opportunities_account.yml'), $response);
    }

    public function testPatchOpportunitiesCustomer()
    {
        $opportunity = $this->getReference('lost_opportunity');

        $customer = $this->getEntityManager()
            ->getRepository(B2bCustomer::class)
            ->findOneByName('Default customer');
        $this->getReferenceRepository()->setReference('default_account_customer', $customer);

        $response = $this->request(
            'PATCH',
            $this->getUrl('oro_rest_api_patch', ['entity' => 'opportunities', 'id' => $opportunity->getId()]),
            [
                'data' => [
                    'id' => (string) $opportunity->getId(),
                    'type' => 'opportunities',
                    'relationships' => [
                        'customer' => [
                            'data' => [
                                'type' => 'b2bcustomers',
                                'id' => (string) $customer->getId(),
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->assertApiResponseStatusCodeEquals($response, 200, 'opportunities', 'update');

        $this->assertResponseContains($this->fixturePath('patch_opportunities_customer.yml'), $response);
    }

    /**
     * @param string $fileName
     *
     * @return string
     */
    protected function fixturePath($fileName)
    {
        return sprintf('%s/DataFixtures/responses/rest_json_api/%s', __DIR__, $fileName);
    }
}
