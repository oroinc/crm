<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\EventListener;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Provider\Lifetime\AmountProvider;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class AccountLifetimeSubscriberTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();
    }

    public function testCreateAccount()
    {
        $em = $this->getEntityManager();

        $account = (new Account())
            ->setName('account');
        $em->persist($account);
        $em->flush();

        $this->assertEquals(0, $this->getAmountProvider()->getAccountLifeTimeValue($account));

        return $account;
    }

    /**
     * @dataProvider accountOpportunitiesProvider
     * @depends testCreateAccount
     */
    public function testAccountOpportunities($updateDataCb, $expectedResult, Account $account)
    {
        call_user_func($updateDataCb, $account);

        $this->assertEquals($expectedResult, $this->getAmountProvider()->getAccountLifeTimeValue($account));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function accountOpportunitiesProvider()
    {
        $opportunities = [];

        return [
            'CreateOpenOpportunityRelatedToTheAccount' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $opportunities['op1'] = (new Opportunity())
                        ->setName('op1')
                        ->setStatus($this->findOpportunityStatus('in_progress'))
                        ->setCustomerAssociation(
                            (new Customer())
                                ->setTarget($account)
                        );
                    $em->persist($opportunities['op1']);
                    $em->flush();
                },
                0,
            ],
            'CloseTheAccountOpportunityAsWon' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $opportunities['op1']
                        ->setCloseRevenueValue(5)
                        ->setStatus($this->findOpportunityStatus(Opportunity::STATUS_WON));
                    $em->flush();
                },
                5,
            ],
            'CreateOpenOpportunityRelatedToB2BCustomer' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $b2bCustomer = (new B2bCustomer())
                        ->setName('customer')
                        ->setAccount($account);
                    $em->persist($b2bCustomer);

                    $opportunities['op2'] = (new Opportunity())
                        ->setName('op2')
                        ->setStatus($this->findOpportunityStatus('in_progress'))
                        ->setCustomerAssociation(
                            (new Customer())
                                ->setTarget($account, $b2bCustomer)
                        );
                    $em->persist($opportunities['op2']);
                    $em->flush();
                },
                5,
            ],
            'CloseTheB2BCustomerOpportunityAsWon' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $opportunities['op2']
                        ->setCloseRevenueValue(15)
                        ->setStatus($this->findOpportunityStatus(Opportunity::STATUS_WON));
                    $em->flush();
                },
                5,
            ],
            'CreateClosedAsWonOpportunityRelatedToTheAccount' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $opportunities['op3'] = (new Opportunity())
                        ->setName('op3')
                        ->setCloseRevenueValue(3)
                        ->setStatus($this->findOpportunityStatus(Opportunity::STATUS_WON))
                        ->setCustomerAssociation(
                            (new Customer())
                                ->setTarget($account)
                        );
                    $em->persist($opportunities['op3']);
                    $em->flush();
                },
                8,
            ],
            'RemoveOpportunityOp3' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $em->remove($opportunities['op3']);
                    $em->flush();
                },
                5,
            ],
            'RemoveOpportunityOp2' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $em->remove($opportunities['op2']);
                    $em->flush();
                },
                5,
            ],
            'ChangeOpportunityOp1Status Won->Lost' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $opportunities['op1']
                        ->setStatus($this->findOpportunityStatus(Opportunity::STATUS_LOST));
                    $em->flush();
                },
                0,
            ],
            'ChangeOpportunityOp1Status Lost->Won' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $opportunities['op1']
                        ->setStatus($this->findOpportunityStatus(Opportunity::STATUS_WON));
                    $em->flush();
                },
                5,
            ],
            'ChangeOpportunityOp1Relation $account->$account2 (change including customerAssociation)' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $account2 = (new Account())
                        ->setName('account2');
                    $em->persist($account);

                    $opportunities['op1']
                        ->setCustomerAssociation(
                            (new Customer)
                                ->setTarget($account2)
                        );
                    $em->flush();
                },
                0,
            ],
            'ChangeOpportunityOp1Relation $account2->$account (change including customerAssociation)' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $opportunities['op1']
                        ->setCustomerAssociation(
                            (new Customer())
                                ->setTarget($account)
                        );
                    $em->flush();
                },
                5,
            ],
            'ChangeOpportunityOp1Relation $account->$account2' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $account2 = (new Account())
                        ->setName('account2');
                    $em->persist($account);

                    $opportunities['op1']
                        ->getCustomerAssociation()
                        ->setTarget($account2);
                    $em->flush();
                },
                0,
            ],
            'ChangeOpportunityOp1Relation $account2->$account' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $opportunities['op1']
                        ->getCustomerAssociation()
                        ->setTarget($account);
                    $em->flush();
                },
                5,
            ],
            'ChangeOpportunityOp1Relation $account->$b2bCustomer (so account is unchanged)' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $b2bCustomer = (new B2bCustomer())
                        ->setName('customer')
                        ->setAccount($account);
                    $em->persist($b2bCustomer);

                    $opportunities['op1']
                        ->getCustomerAssociation()
                        ->setTarget($account, $b2bCustomer);
                    $em->flush();
                },
                0,
            ],
            'RemoveOpportunityOp1' => [
                function (Account $account) use (&$opportunities) {
                    $em = $this->getEntityManager();

                    $em->remove($opportunities['op1']);
                    $em->flush();
                },
                0,
            ],
        ];
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @return AmountProvider
     */
    protected function getAmountProvider()
    {
        return $this->getContainer()->get('oro_channel.provider.lifetime.amount_provider');
    }

    /**
     * @param string $code
     *
     * @return AbstractEnumValue
     */
    protected function findOpportunityStatus($code)
    {
        $status = $this->getEntityManager()
            ->getRepository(ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATUS_CODE))
            ->find($code);

        if (!$status) {
            throw new \InvalidArgumentException(sprintf('Status "%s" doesn\'t exists', $code));
        }

        return $status;
    }
}
