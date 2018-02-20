<?php

namespace Oro\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractDemoFixture extends AbstractFixture implements ContainerAwareInterface
{
    /** @var  EntityManager */
    protected $em;

    /** @var ContainerInterface */
    protected $container;

    /** @var array */
    private $userIds;

    /** @var int */
    private $usersCount = 0;

    /** @var array ['country ISO2 code' => ['region code' => 'region combined code']] */
    private $regionByCountryMap;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->em        = $container->get('doctrine')->getManager();
        $this->container = $container;
    }

    /**
     * @return User
     */
    protected function getRandomUserReference()
    {
        if (null === $this->userIds) {
            $this->userIds = $this->loadUserIds();
            shuffle($this->userIds);
            $this->usersCount = count($this->userIds) - 1;
        }

        $random = rand(0, $this->usersCount);

        return $this->getUserReference($this->userIds[$random]);
    }

    /**
     * @param int $id
     *
     * @return User
     */
    protected function getUserReference($id)
    {
        return $this->em->getReference('OroUserBundle:User', $id);
    }

    /**
     * @param string $code ISO2 code
     *
     * @return Country
     */
    protected function getCountryReference($code)
    {
        return $this->em->getReference('OroAddressBundle:Country', $code);
    }

    /**
     * @param string $countryCode ISO2 code
     * @param string $code        region code
     *
     * @return null|Region
     */
    protected function getRegionReference($countryCode, $code)
    {
        if (null === $this->regionByCountryMap) {
            $this->regionByCountryMap = $this->loadRegionByCountryMap();
        }

        return isset($this->regionByCountryMap[$countryCode], $this->regionByCountryMap[$countryCode][$code])
            ?
            $this->em->getReference('OroAddressBundle:Region', $this->regionByCountryMap[$countryCode][$code])
            :
            null;
    }

    /**
     * @return array
     */
    private function loadUserIds()
    {
        $items = $this->em->createQueryBuilder()
            ->from('OroUserBundle:User', 'u')
            ->select('u.id')
            ->getQuery()
            ->getArrayResult();

        return array_map(
            function ($item) {
                return $item['id'];
            },
            $items
        );
    }

    /**
     * @return array
     */
    private function loadRegionByCountryMap()
    {
        $items = $this->em->createQueryBuilder()
            ->from('OroAddressBundle:Country', 'c')
            ->leftJoin('c.regions', 'r')
            ->select(['c.iso2Code', 'r.code', 'r.combinedCode'])
            ->getQuery()
            ->getArrayResult();

        $map = [];
        foreach ($items as $item) {
            $map[$item['iso2Code']] = isset($map[$item['iso2Code']]) ? $map[$item['iso2Code']] : [];

            if (isset($item['code'], $item['combinedCode'])) {
                $map[$item['iso2Code']][$item['code']] = $item['combinedCode'];
            }
        }

        return $map;
    }
}
