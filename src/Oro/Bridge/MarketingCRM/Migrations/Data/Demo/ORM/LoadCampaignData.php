<?php

namespace Oro\Bridge\MarketingCRM\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\CampaignBundle\Entity\Campaign;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load campaign demo data
 */
class LoadCampaignData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var User[]
     */
    protected $users;

    /**
     * @var Lead[]
     */
    protected $leads;

    /**
     * @var Organization
     */
    protected $organization;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadLeadsData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->organization = $this->getReference('default_organization');
        $this->users = $manager->getRepository('OroUserBundle:User')->findAll();
        $this->leads = $manager->getRepository('OroSalesBundle:Lead')->findAll();

        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@OroDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');

        $handle = fopen($dictionaryDir . DIRECTORY_SEPARATOR. "campaigns.csv", "r");
        if ($handle) {
            $headers = array();
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $randomUser = count($this->users) - 1;

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $user = $this->users[mt_rand(0, $randomUser)];

                $this->setSecurityContext($user);

                $data = array_combine($headers, array_values($data));

                $campaign = $this->createCampaign($data, $user);
                $leadsNumber = mt_rand(1, 10);
                for ($i = 0; $i <= $leadsNumber; $i++) {
                    $lead = $this->getLead();
                    $lead->setCampaign($campaign);
                    $manager->persist($lead);
                }
                $manager->persist($campaign);
            }

            $manager->flush();

            fclose($handle);

            $tokenStorage = $this->container->get('security.token_storage');
            $tokenStorage->setToken(null);
        }
    }

    /**
     * @return Lead
     */
    protected function getLead()
    {
        /**
         * @var Lead
         */
        $lead = $this->leads[mt_rand(0, count($this->leads) - 1)];
        if ($lead->getCampaign()) {
            return $this->getLead();
        }

        return $lead;
    }

    protected function createCampaign(array $data, $user)
    {
        $campaign = new Campaign();
        $campaign->setName($data['Name']);
        $campaign->setCode($data['Code']);
        $campaign->setBudget($data['Budget']);
        $campaign->setOwner($user);
        $campaign->setOrganization($this->organization);
        return $campaign;
    }

    /**
     * @param User $user
     */
    protected function setSecurityContext($user)
    {
        $tokenStorage = $this->container->get('security.token_storage');
        $token = new UsernamePasswordOrganizationToken($user, $user->getUsername(), 'main', $this->organization);
        $tokenStorage->setToken($token);
    }
}
