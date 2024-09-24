<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\DataGridBundle\Extension\Appearance\AppearanceExtension;
use Oro\Bundle\DataGridBundle\Extension\Columns\ColumnsExtension;
use Oro\Bundle\DataGridBundle\Extension\GridViews\GridViewsExtension;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\DataGridBundle\Extension\Sorter\AbstractSorterExtension;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EnumFilterType;
use Oro\Bundle\FilterBundle\Grid\Extension\AbstractFilterExtension;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Model\ChangeLeadStatus;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Loads pin tabs.
 */
class LoadPintabsData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    #[\Override]
    public function getDependencies(): array
    {
        return [LoadUserData::class];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $navigationFactory = $this->container->get('oro_navigation.item.factory');
        $tokenStorage = $this->container->get('security.token_storage');
        $userOrganization = $manager->getRepository(Organization::class)->getFirst();
        $pinTabOrganization = $this->getReference('default_organization');

        $users = $manager->getRepository(User::class)->findAll();
        $params = $this->getPintabsParams();
        foreach ($users as $user) {
            $tokenStorage->setToken(new UsernamePasswordOrganizationToken(
                $user,
                'main',
                $userOrganization,
                $user->getUserRoles()
            ));
            foreach ($params as $param) {
                $param['user'] = $user;
                $pinTab = $navigationFactory->createItem($param['type'], $param);
                $pinTab->getItem()->setOrganization($pinTabOrganization);
                $manager->persist($pinTab);
            }
            $tokenStorage->setToken(null);
        }
        $manager->flush();
    }

    private function getPintabsParams(): array
    {
        $router = $this->container->get('router');
        $datagridRouter = $this->container->get('oro_datagrid.helper.route');

        return [
            'account' => [
                'url' => $router->generate('oro_account_index'),
                'title_rendered' => 'Accounts - Customers',
                'title' => '{"template":"Accounts - Customers","short_template":"Accounts","params":[]}',
                'position' => 0,
                'type' => 'pinbar',
                'display_type' => 'list',
                'maximized' => false,
                'remove' => false
            ],
            'contact' => [
                'url' => $router->generate('oro_contact_index'),
                'title_rendered' => 'Contacts - Customers',
                'title' => '{"template":"Contacts - Customers","short_template":"Contacts","params":[]}',
                'position' => 1,
                'type' => 'pinbar',
                'display_type' => 'list',
                'maximized' => false,
                'remove' => false
            ],
            'leads' => [
                'url' => $datagridRouter->generate(
                    'oro_sales_lead_index',
                    'sales-lead-grid',
                    [
                        PagerInterface::MINIFIED_PAGE_PARAM => 1,
                        PagerInterface::MINIFIED_PER_PAGE_PARAM => 25,
                        AbstractSorterExtension::MINIFIED_SORTERS_PARAM => ['createdAt' => 1],
                        AbstractFilterExtension::MINIFIED_FILTER_PARAM => [
                            'status' => [
                                'type' => EnumFilterType::TYPE_NOT_IN,
                                'value' => [ChangeLeadStatus::STATUS_QUALIFY, ChangeLeadStatus::STATUS_DISQUALIFY],
                            ]
                        ],
                        ColumnsExtension::MINIFIED_COLUMNS_PARAM => 'id0.name1.status1.firstName1.lastName1.' .
                            'createdAt1.updatedAt0.jobTitle0.companyName0.industry0.website0.numberOfEmployees0.' .
                            'source0.email1.phone1.countryName1.regionLabel1.addressPostalCode1.addressCity0.' .
                            'addressStreet0.ownerName1.contactName0.twitter0.linkedIn0.timesContacted0.' .
                            'timesContactedIn0.timesContactedOut0.lastContactedDate0.lastContactedDateIn0.' .
                            'lastContactedDateOut0.daysSinceLastContact0.tags1',
                        GridViewsExtension::MINIFIED_VIEWS_PARAM_KEY => 'lead.open',
                        AppearanceExtension::MINIFIED_APPEARANCE_TYPE_PARAM => 'grid'
                    ]
                ),
                'title_rendered' => 'Leads - Sales',
                'title' => '{"template":"Leads - Sales","short_template":"Leads","params":[]}',
                'position' => 2,
                'type' => 'pinbar',
                'display_type' => 'list',
                'maximized' => false,
                'remove' => false
            ],
            'opportunities' => [
                'url' => $datagridRouter->generate(
                    'oro_sales_opportunity_index',
                    'sales-opportunity-grid',
                    [
                        PagerInterface::MINIFIED_PAGE_PARAM => 1,
                        PagerInterface::MINIFIED_PER_PAGE_PARAM => 25,
                        AbstractSorterExtension::MINIFIED_SORTERS_PARAM => ['createdAt' => 1],
                        AbstractFilterExtension::MINIFIED_FILTER_PARAM => [
                            'status' => [
                                'type'  => EnumFilterType::TYPE_NOT_IN,
                                'value' => [Opportunity::STATUS_WON, Opportunity::STATUS_LOST]
                            ]
                        ],
                        ColumnsExtension::MINIFIED_COLUMNS_PARAM => 'id0.name1.createdAt1.updatedAt0.contactName1.' .
                            'closeRevenue0.closeRevenueBaseCurrency0.closeReasonLabel0.closeDate1.' .
                            'budgetAmount1.budgetAmountBaseCurrency1.probability1.status1.primaryEmail1.' .
                            'ownerName1.accountName0.timesContacted0.timesContactedIn0.timesContactedOut0.' .
                            'lastContactedDate0.lastContactedDateIn0.lastContactedDateOut0.daysSinceLastContact0.tags1',
                        GridViewsExtension::MINIFIED_VIEWS_PARAM_KEY => 'opportunity.open',
                        AppearanceExtension::MINIFIED_APPEARANCE_TYPE_PARAM => 'grid'
                    ]
                ),
                'title_rendered' => 'Opportunities - Sales',
                'title' => '{"template":"Opportunities - Sales","short_template":"Opportunities","params":[]}',
                'position' => 3,
                'type' => 'pinbar',
                'display_type' => 'list',
                'maximized' => false,
                'remove' => false
            ]
        ];
    }
}
