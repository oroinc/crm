<?php
namespace OroCRM\Bundle\DemoDataBundle\DataFixtures;

use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Entity\TagManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

use OroCRM\Bundle\DemoDataBundle\DataFixtures\AbstractFlexibleFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\ContactBundle\Entity\Source;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class LoadTagsData extends AbstractFlexibleFixture implements ContainerAwareInterface, OrderedFixtureInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Account[]
     */
    protected $accounts;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var User[]
     */
    protected $users;

    /**
     * @var Contact[]
     */
    protected $contacts;

    /**
     * @var EntityManager
     */
    protected $contactManager;

    /** @var  TagManager */
    protected $tagManager;

    /** @var EntityRepository */
    protected $tagsRepository;

    /** @var Tag[] */
    protected $tagsUser;

    /** @var Tag[] */
    protected $tagsAccount;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->userManager = $container->get('oro_user.manager');
        $this->tagManager = $container->get('oro_tag.tag.manager');
        $this->contactManager = $container->get('doctrine.orm.entity_manager');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->initSupportingEntities();
        $this->loadUsersTags();
        $this->loadAccountsTags();
        $this->loadContactsTags();
    }

    protected function initSupportingEntities()
    {
        $userStorageManager = $this->userManager->getStorageManager();
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $this->users = $userStorageManager->getRepository('OroUserBundle:User')->findAll();

        $this->accounts = $this->contactManager->getRepository('OroCRMAccountBundle:Account')->findAll();
        $this->contacts = $this->contactManager->getRepository('OroCRMContactBundle:Contact')->findAll();

        $this->tagsRepository = $entityManager->getRepository('OroTagBundle:Tag');
    }


    /**
     * @param $tagsNames
     * @return Tag[]
     */
    protected function createTags($tagsNames)
    {
        $tags = array();
        foreach ($tagsNames as $tagName) {
            $tags[] = new Tag($tagName);
        }
        return $tags;
    }

    public function loadUsersTags()
    {
        $this->tagsUser = $this->createTags(array('Friends', 'Developer', 'Wholesale'));
        foreach ($this->users as $user) {
            $securityContext = $this->container->get('security.context');
            $token = new UsernamePasswordToken($user, '123123q', 'main');
            $securityContext->setToken($token);

            $ownTag = array($this->tagsUser[rand(0, count($this->tagsUser)-1)]);
            $user->setTags(
                array(
                    'owner' => $ownTag,
                    'all' => array()
                )
            );
            $this->persist($this->userManager->getStorageManager(), $user);
            $this->tagManager->saveTagging($user);
        }
        $this->flush($this->userManager->getStorageManager());
    }

    public function loadAccountsTags()
    {
        $this->tagsAccount = $this->createTags(
            array(
                'Commercial',
                'Business',
                'Vendor',
                'Gold Partner',
                'Service',
                '#new',
                '#vip',
                '#popular',
                '#call',
                '#discontinued',
                'Premium')
        );
        foreach ($this->accounts as $account) {
            $user = $this->users[rand(0, count($this->users)-1)];

            $securityContext = $this->container->get('security.context');
            $token = new UsernamePasswordToken($user, '123123q', 'main');
            $securityContext->setToken($token);

            $ownTags = array(
                $this->tagsUser[rand(0, count($this->tagsUser)-1)],
                $this->tagsAccount[rand(0, count($this->tagsAccount)-1)]
            );

            $account->setTags(
                array(
                    'owner' => $ownTags ,
                    'all' => array()
                )
            );
            $this->persist($this->contactManager, $account);
            $this->tagManager->saveTagging($account);
        }
        $this->flush($this->contactManager);
    }

    public function loadContactsTags()
    {
        foreach ($this->contacts as $contact) {
            $user = $this->users[rand(0, count($this->users)-1)];

            $securityContext = $this->container->get('security.context');
            $token = new UsernamePasswordToken($user, '123123q', 'main');
            $securityContext->setToken($token);

            $ownTags = array(
                $this->tagsUser[rand(0, count($this->tagsUser)-1)],
                $this->tagsAccount[rand(0, count($this->tagsAccount)-1)]
            );

            $contact->setTags(
                array(
                    'owner' => $ownTags,
                    'all' => array()
                )
            );
            $this->persist($this->contactManager, $contact);
            $this->tagManager->saveTagging($contact);
        }
        $this->flush($this->contactManager);
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

    public function getOrder()
    {
        return 300;
    }
}
