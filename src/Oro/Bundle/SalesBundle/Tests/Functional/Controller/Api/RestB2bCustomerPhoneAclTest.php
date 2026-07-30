<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller\Api;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerEntitiesData;
use Oro\Bundle\SalesBundle\Tests\Functional\DataFixtures\LoadB2bCustomerPhoneData;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Test\Functional\RolePermissionExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * @dbIsolationPerTest
 */
class RestB2bCustomerPhoneAclTest extends WebTestCase
{
    use RolePermissionExtension;

    #[\Override]
    protected function setUp(): void
    {
        $this->initClient([], self::generateApiAuthHeader());
        $this->loadFixtures([LoadB2bCustomerPhoneData::class]);
    }

    public function testGetPhoneListGrantedWithViewPermission(): void
    {
        $customer = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::FIRST_ENTITY_NAME);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_b2bcustomer_phones', ['customerId' => $customer->getId()])
        );

        $data = self::getJsonResponseContent($this->client->getResponse(), Response::HTTP_OK);
        self::assertNotEmpty($data);
    }

    public function testGetPrimaryPhoneGrantedWithViewPermission(): void
    {
        $customer = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::FIRST_ENTITY_NAME);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_b2bcustomer_phone_primary', ['customerId' => $customer->getId()])
        );

        self::assertResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_OK);
    }

    public function testGetPhoneListDeniedWhenViewPermissionRevoked(): void
    {
        $this->updateRolePermission(
            User::ROLE_ADMINISTRATOR,
            B2bCustomer::class,
            AccessLevel::NONE_LEVEL
        );

        $customer = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::FIRST_ENTITY_NAME);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_b2bcustomer_phones', ['customerId' => $customer->getId()])
        );

        self::assertJsonResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_FORBIDDEN);
    }

    public function testGetPrimaryPhoneDeniedWhenViewPermissionRevoked(): void
    {
        $this->updateRolePermission(
            User::ROLE_ADMINISTRATOR,
            B2bCustomer::class,
            AccessLevel::NONE_LEVEL
        );

        $customer = $this->getReference('B2bCustomer_' . LoadB2bCustomerEntitiesData::FIRST_ENTITY_NAME);

        $this->client->request(
            'GET',
            $this->getUrl('oro_api_get_b2bcustomer_phone_primary', ['customerId' => $customer->getId()])
        );

        self::assertJsonResponseStatusCodeEquals($this->client->getResponse(), Response::HTTP_FORBIDDEN);
    }
}
