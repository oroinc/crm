<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;

use OroCRM\Bundle\SalesBundle\Entity\LeadPhone;
use OroCRM\Bundle\SalesBundle\Entity\Lead;

class LoadLeadsData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    const FLUSH_MAX = 50;

    /** @var ContainerInterface */
    protected $container;

    /** @var User[] */
    protected $users;

    /** @var Country[] */
    protected $countries;

    /** @var  EntityManager */
    protected $em;

    /** @var  ConfigManager */
    protected $configManager;

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
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadUsersData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadAccountData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadLeadSourceData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadChannelData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->configManager = $container->get('oro_entity_config.config_manager');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities($manager);
        $this->loadLeads($manager);
        $this->loadSources($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    protected function initSupportingEntities(ObjectManager $manager = null)
    {
        if ($manager) {
            $this->em = $manager;
        }

        $this->users = $this->em->getRepository('OroUserBundle:User')->findAll();
        $this->countries = $this->em->getRepository('OroAddressBundle:Country')->findAll();
        $this->organization = $this->getReference('default_organization');
    }

    /**
     * @param ObjectManager $manager
     */
    public function loadSources(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName('lead_source');

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);

        /** @var AbstractEnumValue[] $sources */
        $sources = $enumRepo->findAll();
        $randomSource = count($sources)-1;

        $leads = $this->em->getRepository('OroCRMSalesBundle:Lead')->findAll();

        foreach ($leads as $lead) {
            /** @var Lead $lead */
            $source = $sources[mt_rand(0, $randomSource)];
            $lead->setSource($source);
            $manager->persist($lead);
        }
        $manager->flush();
    }

    public function loadLeads(ObjectManager $manager)
    {
        $dictionaryDir = $this->container
            ->get('kernel')
            ->locateResource('@OroCRMDemoDataBundle/Migrations/Data/Demo/ORM/dictionaries');

        $handle = fopen($dictionaryDir . DIRECTORY_SEPARATOR. "leads.csv", "r");
        if ($handle) {
            $headers = array();
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                //read headers
                $headers = $data;
            }
            $randomUser = count($this->users) - 1;
            $i = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $user = $this->users[mt_rand(0, $randomUser)];
                $this->setSecurityContext($user);

                $data = array_combine($headers, array_values($data));

                $lead = $this->createLead($manager, $data, $user);
                $this->persist($this->em, $lead);

                $i++;
                if ($i % self::FLUSH_MAX == 0) {
                    $this->flush($this->em);
                }
            }

            $this->flush($this->em);
            fclose($handle);
        }
    }

    /**
     * @param User $user
     */
    protected function setSecurityContext($user)
    {
        $securityContext = $this->container->get('security.context');
        $token = new UsernamePasswordOrganizationToken($user, $user->getUsername(), 'main', $this->organization);
        $securityContext->setToken($token);
    }

    /**
     * @param  array $data
     * @param User $user
     *
     * @return Lead
     */
    protected function createLead(ObjectManager $manager, array $data, $user)
    {
        $lead = new Lead();

        $className = ExtendHelper::buildEnumValueClassName(Lead::INTERNAL_STATUS_CODE);
        $defaultStatus = $manager->getRepository($className)->find(ExtendHelper::buildEnumValueId('new'));
        
        $lead->setStatus($defaultStatus);
        $lead->setName($data['Company']);
        $lead->setFirstName($data['GivenName']);
        $lead->setLastName($data['Surname']);
        $lead->setEmail($data['EmailAddress']);

        $phone = new LeadPhone($data['TelephoneNumber']);
        $phone->setPrimary(true);
        $lead->addPhone($phone);

        $lead->setCompanyName($data['Company']);
        $lead->setOwner($user);
        $lead->setOrganization($this->organization);
        $lead->setDataChannel($this->getReference('default_channel'));

        /** @var Address $address */
        $address = new Address();
        $address->setLabel('Primary Address');
        $address->setCity($data['City']);
        $address->setStreet($data['StreetAddress']);
        $address->setPostalCode($data['ZipCode']);
        $address->setFirstName($data['GivenName']);
        $address->setLastName($data['Surname']);

        $isoCode = $data['Country'];
        $country = array_filter(
            $this->countries,
            function (Country $a) use ($isoCode) {
                return $a->getIso2Code() == $isoCode;
            }
        );

        $country = array_values($country);
        /** @var Country $country */
        $country = $country[0];

        $idRegion = $data['State'];
        /** @var Collection $regions */
        $regions = $country->getRegions();

        $region = $regions->filter(
            function (Region $a) use ($idRegion) {
                return $a->getCode() == $idRegion;
            }
        );

        $address->setCountry($country);
        if (!$region->isEmpty()) {
            $address->setRegion($region->first());
        }

        $lead->setAddress($address);

        return $lead;
    }

    /**
     * Persist object
     *
     * @param mixed $manager
     * @param mixed $object
     */
    private function persist($manager, $object)
    {
        $manager->persist($object);
    }

    /**
     * Flush objects
     *
     * @param mixed $manager
     */
    private function flush($manager)
    {
        $manager->flush();
    }
}
